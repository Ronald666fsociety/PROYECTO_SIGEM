#!/usr/bin/env python3
"""Motor predictivo de SIGEM alineado con el perfil de Proyecto de Grado.

El aporte obligatorio es Holt lineal sin estacionalidad. Su desempeno se compara,
sin presuponer superioridad, con ultimo valor, suavizamiento exponencial simple y
regresion lineal temporal mediante evaluacion temporal expansiva.
"""

from __future__ import annotations

import argparse
import json
import sqlite3
import sys
import warnings
from collections.abc import Callable
from pathlib import Path

import numpy as np
import pandas as pd
from statsmodels.tsa.holtwinters import Holt, SimpleExpSmoothing

warnings.filterwarnings("ignore")

MESES_ES = {
    1: "Ene",
    2: "Feb",
    3: "Mar",
    4: "Abr",
    5: "May",
    6: "Jun",
    7: "Jul",
    8: "Ago",
    9: "Sep",
    10: "Oct",
    11: "Nov",
    12: "Dic",
}

MESES_MINIMOS = 36
MESES_COMPROBACION_FINAL = 6
ENTRENAMIENTO_INICIAL = 18
HORIZONTE_EVALUACION = 6
IGLESIAS_ESPERADAS = 24

METODOS = {
    "holt": "Holt lineal",
    "ultimo_valor": "Ultimo valor observado",
    "suavizamiento_simple": "Suavizamiento exponencial simple",
    "regresion_lineal": "Regresion lineal temporal",
}


def _tiene_columna(conexion: sqlite3.Connection, tabla: str, columna: str) -> bool:
    columnas = conexion.execute(f"PRAGMA table_info({tabla})").fetchall()
    return any(fila[1] == columna for fila in columnas)


def obtener_serie_distrital(db_path: str) -> pd.DataFrame:
    """Obtiene un unico total mensual distrital y conserva su procedencia."""
    conexion = sqlite3.connect(db_path)
    try:
        expresion_sintetica = (
            "SUM(CASE WHEN es_sintetico = 1 THEN 1 ELSE 0 END)"
            if _tiene_columna(conexion, "conteos_membresia", "es_sintetico")
            else "0"
        )
        consulta = f"""
            SELECT
                anio,
                mes,
                SUM(total_activos) AS total_activos,
                COUNT(DISTINCT iglesia_id) AS iglesias_reportadas,
                COUNT(*) AS registros_iglesia_mes,
                {expresion_sintetica} AS registros_sinteticos
            FROM conteos_membresia
            WHERE estado IN ('cerrado', 'validado')
            GROUP BY anio, mes
            ORDER BY anio, mes
        """
        serie = pd.read_sql_query(consulta, conexion)
    finally:
        conexion.close()

    return preparar_serie_distrital(serie)


def obtener_serie_json(input_path: str) -> pd.DataFrame:
    """Carga la serie agregada exportada por Laravel sin depender del motor SQL."""
    contenido = json.loads(Path(input_path).read_text(encoding="utf-8"))
    filas = contenido.get("serie", contenido) if isinstance(contenido, dict) else contenido
    if not isinstance(filas, list):
        raise ValueError("El archivo de entrada no contiene una serie mensual valida.")

    return preparar_serie_distrital(pd.DataFrame(filas))


def preparar_serie_distrital(serie: pd.DataFrame) -> pd.DataFrame:
    """Normaliza la serie agregada proveniente de SQLite, MySQL o un archivo JSON."""
    if serie.empty:
        return pd.DataFrame()

    columnas_requeridas = {
        "anio",
        "mes",
        "total_activos",
        "iglesias_reportadas",
        "registros_sinteticos",
    }
    faltantes = columnas_requeridas.difference(serie.columns)
    if faltantes:
        raise ValueError(
            "Faltan columnas requeridas en la serie: " + ", ".join(sorted(faltantes))
        )

    serie["periodo"] = pd.to_datetime(
        serie.apply(
            lambda fila: f"{int(fila['anio'])}-{int(fila['mes']):02d}-01", axis=1
        )
    )
    serie = serie.sort_values("periodo").reset_index(drop=True)
    serie["total_activos"] = serie["total_activos"].astype(float)
    serie["iglesias_reportadas"] = serie["iglesias_reportadas"].astype(int)
    serie["registros_sinteticos"] = serie["registros_sinteticos"].astype(int)
    return serie


def verificar_compuerta(serie: pd.DataFrame) -> dict:
    """Comprueba suficiencia, continuidad y cobertura; nunca rellena faltantes."""
    if serie.empty:
        return {
            "aprobada": False,
            "motivo": "No existen cortes mensuales cerrados o validados.",
            "meses_disponibles": 0,
        }

    serie = serie.sort_values("periodo").reset_index(drop=True)
    meses_disponibles = len(serie)
    fechas_esperadas = pd.date_range(
        start=serie["periodo"].min(), end=serie["periodo"].max(), freq="MS"
    )
    periodos_disponibles = set(serie["periodo"])
    periodos_faltantes = [
        fecha.strftime("%Y-%m")
        for fecha in fechas_esperadas
        if fecha not in periodos_disponibles
    ]
    cobertura_incompleta = serie.loc[
        serie["iglesias_reportadas"] != IGLESIAS_ESPERADAS,
        ["periodo", "iglesias_reportadas"],
    ]
    cortes_incompletos = [
        {
            "periodo": fila.periodo.strftime("%Y-%m"),
            "iglesias_reportadas": int(fila.iglesias_reportadas),
        }
        for fila in cobertura_incompleta.itertuples()
    ]
    contiene_sinteticos = bool((serie["registros_sinteticos"] > 0).any())

    detalle = {
        "meses_disponibles": meses_disponibles,
        "meses_minimos": MESES_MINIMOS,
        "periodo_inicial": serie.iloc[0]["periodo"].strftime("%Y-%m"),
        "periodo_final": serie.iloc[-1]["periodo"].strftime("%Y-%m"),
        "periodos_faltantes": periodos_faltantes,
        "cortes_con_cobertura_incompleta": cortes_incompletos,
        "iglesias_esperadas_por_corte": IGLESIAS_ESPERADAS,
        "contiene_datos_sinteticos": contiene_sinteticos,
        "aprobada_para_validacion_real": False,
    }

    if meses_disponibles < MESES_MINIMOS:
        return {
            **detalle,
            "aprobada": False,
            "motivo": (
                f"Serie insuficiente: {meses_disponibles} meses disponibles; "
                f"se requieren al menos {MESES_MINIMOS}."
            ),
        }

    if periodos_faltantes:
        return {
            **detalle,
            "aprobada": False,
            "motivo": (
                "La serie no es mensual y continua. No se imputan meses faltantes: "
                + ", ".join(periodos_faltantes)
                + "."
            ),
        }

    if cortes_incompletos:
        primer_corte = cortes_incompletos[0]
        return {
            **detalle,
            "aprobada": False,
            "motivo": (
                "Cada total distrital debe incluir las 24 iglesias. "
                f"El corte {primer_corte['periodo']} contiene "
                f"{primer_corte['iglesias_reportadas']}."
            ),
        }

    return {
        **detalle,
        "aprobada": True,
        "aprobada_para_validacion_real": not contiene_sinteticos,
        "motivo": (
            "La serie supera la compuerta estructural, pero contiene datos "
            "sinteticos y solo permite una prueba funcional."
            if contiene_sinteticos
            else "La serie supera la compuerta de calidad para evaluacion temporal."
        ),
    }


def _pronosticar_holt(entrenamiento: np.ndarray, horizonte: int) -> np.ndarray:
    modelo = Holt(
        entrenamiento, damped_trend=False, initialization_method="estimated"
    ).fit(optimized=True)
    return np.asarray(modelo.forecast(horizonte), dtype=float)


def _pronosticar_ultimo_valor(entrenamiento: np.ndarray, horizonte: int) -> np.ndarray:
    return np.repeat(float(entrenamiento[-1]), horizonte)


def _pronosticar_suavizamiento_simple(
    entrenamiento: np.ndarray, horizonte: int
) -> np.ndarray:
    modelo = SimpleExpSmoothing(
        entrenamiento, initialization_method="estimated"
    ).fit(optimized=True)
    return np.asarray(modelo.forecast(horizonte), dtype=float)


def _pronosticar_regresion_lineal(
    entrenamiento: np.ndarray, horizonte: int
) -> np.ndarray:
    eje_entrenamiento = np.arange(len(entrenamiento), dtype=float)
    pendiente, intercepto = np.polyfit(eje_entrenamiento, entrenamiento, 1)
    eje_futuro = np.arange(
        len(entrenamiento), len(entrenamiento) + horizonte, dtype=float
    )
    return pendiente * eje_futuro + intercepto


PRONOSTICADORES: dict[str, Callable[[np.ndarray, int], np.ndarray]] = {
    "holt": _pronosticar_holt,
    "ultimo_valor": _pronosticar_ultimo_valor,
    "suavizamiento_simple": _pronosticar_suavizamiento_simple,
    "regresion_lineal": _pronosticar_regresion_lineal,
}


def _registro_error(
    etapa: str,
    metodo: str,
    origen: int,
    periodo_origen: pd.Timestamp,
    horizonte: int,
    periodo_objetivo: pd.Timestamp,
    real: float,
    prediccion: float,
) -> dict:
    error = real - prediccion
    return {
        "etapa": etapa,
        "metodo": metodo,
        "nombre_metodo": METODOS[metodo],
        "origen": origen,
        "periodo_origen": periodo_origen.strftime("%Y-%m"),
        "horizonte": horizonte,
        "periodo_objetivo": periodo_objetivo.strftime("%Y-%m"),
        "real": float(real),
        "prediccion": float(prediccion),
        "error": float(error),
        "error_absoluto": float(abs(error)),
        "error_cuadratico": float(error**2),
    }


def _resumir_registros(registros: list[dict]) -> list[dict]:
    resumen = []
    for metodo, nombre in METODOS.items():
        filas = [fila for fila in registros if fila["metodo"] == metodo]
        if not filas:
            continue
        resumen.append(
            {
                "metodo": metodo,
                "nombre_metodo": nombre,
                "observaciones": len(filas),
                "mae": float(np.mean([fila["error_absoluto"] for fila in filas])),
                "rmse": float(
                    np.sqrt(np.mean([fila["error_cuadratico"] for fila in filas]))
                ),
            }
        )
    return resumen


def _resumir_por_horizonte(registros: list[dict]) -> list[dict]:
    resumen = []
    for metodo, nombre in METODOS.items():
        for horizonte in range(1, HORIZONTE_EVALUACION + 1):
            filas = [
                fila
                for fila in registros
                if fila["metodo"] == metodo and fila["horizonte"] == horizonte
            ]
            if not filas:
                continue
            resumen.append(
                {
                    "metodo": metodo,
                    "nombre_metodo": nombre,
                    "horizonte": horizonte,
                    "observaciones": len(filas),
                    "mae": float(
                        np.mean([fila["error_absoluto"] for fila in filas])
                    ),
                    "rmse": float(
                        np.sqrt(
                            np.mean([fila["error_cuadratico"] for fila in filas])
                        )
                    ),
                }
            )
    return resumen


def _evaluar_origen(
    valores: np.ndarray,
    periodos: pd.Series,
    fin_entrenamiento: int,
    etapa: str,
    numero_origen: int,
) -> tuple[list[dict], list[dict]]:
    entrenamiento = valores[:fin_entrenamiento]
    reales = valores[
        fin_entrenamiento : fin_entrenamiento + HORIZONTE_EVALUACION
    ]
    registros: list[dict] = []
    errores_ajuste: list[dict] = []

    for metodo, pronosticador in PRONOSTICADORES.items():
        try:
            predicciones = pronosticador(entrenamiento, HORIZONTE_EVALUACION)
            if len(predicciones) != HORIZONTE_EVALUACION or not np.isfinite(
                predicciones
            ).all():
                raise ValueError("El metodo no produjo seis valores numericos finitos.")
        except Exception as error:  # El fallo se informa; nunca se sustituye el metodo.
            errores_ajuste.append(
                {
                    "etapa": etapa,
                    "origen": numero_origen,
                    "metodo": metodo,
                    "mensaje": str(error),
                }
            )
            continue

        for indice, (real, prediccion) in enumerate(
            zip(reales, predicciones, strict=True), start=1
        ):
            registros.append(
                _registro_error(
                    etapa=etapa,
                    metodo=metodo,
                    origen=numero_origen,
                    periodo_origen=periodos.iloc[fin_entrenamiento - 1],
                    horizonte=indice,
                    periodo_objetivo=periodos.iloc[fin_entrenamiento + indice - 1],
                    real=float(real),
                    prediccion=float(prediccion),
                )
            )
    return registros, errores_ajuste


def validacion_temporal(serie: pd.DataFrame) -> dict:
    """Aplica el protocolo 30+6 y ventanas expansivas definido en el perfil."""
    valores = serie["total_activos"].to_numpy(dtype=float)
    periodos = serie["periodo"]
    meses_totales = len(valores)
    fin_desarrollo = meses_totales - MESES_COMPROBACION_FINAL
    ultimo_origen_desarrollo = fin_desarrollo - HORIZONTE_EVALUACION

    origenes = list(range(ENTRENAMIENTO_INICIAL, ultimo_origen_desarrollo + 1))
    registros_desarrollo: list[dict] = []
    errores_ajuste: list[dict] = []

    for numero_origen, fin_entrenamiento in enumerate(origenes, start=1):
        registros, errores = _evaluar_origen(
            valores=valores,
            periodos=periodos,
            fin_entrenamiento=fin_entrenamiento,
            etapa="desarrollo",
            numero_origen=numero_origen,
        )
        registros_desarrollo.extend(registros)
        errores_ajuste.extend(errores)

    registros_finales, errores_finales = _evaluar_origen(
        valores=valores,
        periodos=periodos,
        fin_entrenamiento=fin_desarrollo,
        etapa="comprobacion_final",
        numero_origen=1,
    )
    errores_ajuste.extend(errores_finales)

    resumen_desarrollo = _resumir_registros(registros_desarrollo)
    resumen_final = _resumir_registros(registros_finales)
    resumen_horizontes = _resumir_por_horizonte(registros_desarrollo)
    metodos_finales = {fila["metodo"] for fila in resumen_final}
    evaluacion_completa = metodos_finales == set(METODOS)

    mejor_metodo = None
    if evaluacion_completa:
        mejor_metodo = min(
            resumen_final, key=lambda fila: (fila["mae"], fila["rmse"])
        )["metodo"]

    return {
        "protocolo": {
            "meses_totales": meses_totales,
            "meses_desarrollo": fin_desarrollo,
            "meses_comprobacion_final": MESES_COMPROBACION_FINAL,
            "entrenamiento_inicial_meses": ENTRENAMIENTO_INICIAL,
            "horizonte_por_origen": HORIZONTE_EVALUACION,
            "numero_origenes_desarrollo": len(origenes),
            "origenes_fin_entrenamiento": origenes,
            "periodo_desarrollo_inicial": periodos.iloc[0].strftime("%Y-%m"),
            "periodo_desarrollo_final": periodos.iloc[
                fin_desarrollo - 1
            ].strftime("%Y-%m"),
            "periodo_comprobacion_inicial": periodos.iloc[
                fin_desarrollo
            ].strftime("%Y-%m"),
            "periodo_comprobacion_final": periodos.iloc[-1].strftime("%Y-%m"),
        },
        "registros_desarrollo": registros_desarrollo,
        "registros_comprobacion_final": registros_finales,
        "resumen_desarrollo": resumen_desarrollo,
        "resumen_por_horizonte": resumen_horizontes,
        "resumen_comprobacion_final": resumen_final,
        "errores_ajuste": errores_ajuste,
        "evaluacion_completa": evaluacion_completa,
        "mejor_metodo": mejor_metodo,
        "nombre_mejor_metodo": METODOS.get(mejor_metodo),
    }


def _metrica_metodo(resumen: list[dict], metodo: str) -> dict | None:
    return next((fila for fila in resumen if fila["metodo"] == metodo), None)


def _valor_parametro(valor: float | None) -> float | None:
    if valor is None or not np.isfinite(valor):
        return None
    return float(valor)


def _ajustar_holt_final(valores: np.ndarray, horizonte: int) -> tuple:
    modelo = Holt(
        valores, damped_trend=False, initialization_method="estimated"
    ).fit(optimized=True)
    return modelo, np.asarray(modelo.forecast(horizonte), dtype=float)


def ejecutar_pronostico_serie(serie: pd.DataFrame, horizonte: int = 6) -> dict:
    horizonte = max(1, min(int(horizonte), HORIZONTE_EVALUACION))
    compuerta = verificar_compuerta(serie)

    if not compuerta["aprobada"]:
        return {
            "estado": "no_evaluable",
            "error": compuerta["motivo"],
            "compuerta_calidad": compuerta,
            "observaciones": (
                "Se conserva la visualizacion historica, pero no se emite una "
                "conclusion de validacion predictiva."
            ),
        }

    evaluacion = validacion_temporal(serie)
    if not evaluacion["evaluacion_completa"]:
        return {
            "estado": "no_evaluable",
            "error": (
                "No fue posible comparar los cuatro metodos en la comprobacion "
                "final bajo las mismas condiciones."
            ),
            "compuerta_calidad": compuerta,
            "evaluacion_temporal": evaluacion,
            "observaciones": (
                "Los errores de ajuste se informan y no se reemplazan por otro metodo."
            ),
        }

    valores = serie["total_activos"].to_numpy(dtype=float)
    try:
        modelo_final, pronostico = _ajustar_holt_final(valores, horizonte)
    except Exception as error:
        return {
            "estado": "no_evaluable",
            "error": f"No fue posible ajustar el modelo Holt final: {error}",
            "compuerta_calidad": compuerta,
            "evaluacion_temporal": evaluacion,
        }

    metrica_holt = _metrica_metodo(
        evaluacion["resumen_comprobacion_final"], "holt"
    )
    rmse_referencia = float(metrica_holt["rmse"]) if metrica_holt else 0.0
    ultimo_corte = serie.iloc[-1]
    ultimo_valor = float(ultimo_corte["total_activos"])
    fecha_ultimo_corte = pd.Timestamp(ultimo_corte["periodo"])
    advertencias: list[str] = []
    pronosticos = []

    for indice, prediccion in enumerate(pronostico, start=1):
        fecha = fecha_ultimo_corte + pd.DateOffset(months=indice)
        prediccion_cruda = float(prediccion)
        margen_aproximado = rmse_referencia * np.sqrt(indice)
        limite_inferior = prediccion_cruda - margen_aproximado
        limite_superior = prediccion_cruda + margen_aproximado
        crecimiento_absoluto = prediccion_cruda - ultimo_valor
        crecimiento_porcentual = (
            (crecimiento_absoluto / ultimo_valor) * 100
            if ultimo_valor > 0
            else None
        )
        if prediccion_cruda < 0 or limite_inferior < 0:
            advertencias.append(
                f"El horizonte {indice} produce un valor negativo crudo o en su "
                "intervalo aproximado; se conserva para transparentar la limitacion."
            )
        pronosticos.append(
            {
                "anio": int(fecha.year),
                "mes": int(fecha.month),
                "periodo": f"{MESES_ES[int(fecha.month)]} {int(fecha.year)}",
                "valor_predicho_crudo": prediccion_cruda,
                "valor_predicho": round(prediccion_cruda, 2),
                "intervalo_aproximado_inferior": round(limite_inferior, 2),
                "intervalo_aproximado_superior": round(limite_superior, 2),
                "crecimiento_absoluto": round(crecimiento_absoluto, 2),
                "crecimiento_porcentual": (
                    round(crecimiento_porcentual, 2)
                    if crecimiento_porcentual is not None
                    else None
                ),
            }
        )

    serie_historica = [
        {
            "anio": int(fila.anio),
            "mes": int(fila.mes),
            "periodo": f"{MESES_ES[int(fila.mes)]} {int(fila.anio)}",
            "total_activos": float(fila.total_activos),
            "iglesias_reportadas": int(fila.iglesias_reportadas),
            "contiene_datos_sinteticos": bool(fila.registros_sinteticos > 0),
        }
        for fila in serie.itertuples()
    ]

    modo_datos = (
        "prueba_funcional"
        if compuerta["contiene_datos_sinteticos"]
        else "evaluacion_real"
    )
    mejor_nombre = evaluacion["nombre_mejor_metodo"]
    observaciones = (
        f"La comprobacion final obtuvo el menor MAE con {mejor_nombre}. "
        "Holt permanece como el aporte predictivo integrado y su desempeno se "
        "informa sin afirmar superioridad previa ni relacion causal."
    )
    if modo_datos == "prueba_funcional":
        observaciones += (
            " Los registros actuales son sinteticos: estos resultados solo "
            "demuestran el funcionamiento tecnico y no validan precision real."
        )

    parametros = modelo_final.params
    return {
        "estado": "evaluado",
        "modo_datos": modo_datos,
        "modelo": "Holt lineal sin estacionalidad",
        "observaciones": observaciones,
        "advertencias": list(dict.fromkeys(advertencias)),
        "compuerta_calidad": compuerta,
        "parametros": {
            "alpha": _valor_parametro(parametros.get("smoothing_level")),
            "beta": _valor_parametro(parametros.get("smoothing_trend")),
            "meses_entrenamiento_final": len(serie),
            "horizonte_meses": horizonte,
            "ultimo_corte": fecha_ultimo_corte.strftime("%Y-%m"),
            "ultimo_valor_real": ultimo_valor,
            "unidad_analisis": "total mensual distrital agregado de 24 iglesias",
        },
        "metricas": {
            "principal": "MAE",
            "secundaria": "RMSE",
            "holt": metrica_holt,
            "mejor_metodo": evaluacion["mejor_metodo"],
            "nombre_mejor_metodo": mejor_nombre,
        },
        "evaluacion_temporal": evaluacion,
        "pronosticos": pronosticos,
        "serie_historica": serie_historica,
    }


def ejecutar_pronostico(db_path: str, horizonte: int = 6) -> dict:
    """Mantiene compatibilidad con las pruebas y ejecuciones locales en SQLite."""
    return ejecutar_pronostico_serie(obtener_serie_distrital(db_path), horizonte)


def ejecutar_pronostico_json(input_path: str, horizonte: int = 6) -> dict:
    """Ejecuta el protocolo con la serie agregada y exportada por Laravel."""
    return ejecutar_pronostico_serie(obtener_serie_json(input_path), horizonte)


def main() -> int:
    parser = argparse.ArgumentParser(description="Modelo predictivo Holt de SIGEM")
    origen = parser.add_mutually_exclusive_group(required=True)
    origen.add_argument("--db-path", help="Ruta a database.sqlite para compatibilidad")
    origen.add_argument(
        "--input-json",
        help="Serie mensual agregada exportada por Laravel desde la base configurada",
    )
    parser.add_argument(
        "--horizonte", type=int, default=6, help="Horizonte entre 1 y 6 meses"
    )
    parser.add_argument("--output", help="Ruta del archivo JSON de salida")
    argumentos = parser.parse_args()

    resultado = (
        ejecutar_pronostico_json(argumentos.input_json, argumentos.horizonte)
        if argumentos.input_json
        else ejecutar_pronostico(argumentos.db_path, argumentos.horizonte)
    )
    salida = json.dumps(resultado, ensure_ascii=False, indent=2)
    if argumentos.output:
        ruta_salida = Path(argumentos.output)
        ruta_salida.parent.mkdir(parents=True, exist_ok=True)
        ruta_salida.write_text(salida, encoding="utf-8")
    print(salida)
    return 0 if resultado["estado"] == "evaluado" else 2


if __name__ == "__main__":
    sys.exit(main())
