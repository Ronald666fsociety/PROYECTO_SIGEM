from __future__ import annotations

import importlib.util
import sqlite3
import tempfile
import unittest
from datetime import date
from pathlib import Path
from unittest.mock import patch


SCRIPT = Path(__file__).parents[2] / "python" / "holt_prediccion.py"
SPEC = importlib.util.spec_from_file_location("holt_prediccion", SCRIPT)
assert SPEC and SPEC.loader
holt_prediccion = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(holt_prediccion)


def avanzar_mes(inicio: date, desplazamiento: int) -> tuple[int, int]:
    indice = inicio.year * 12 + inicio.month - 1 + desplazamiento
    return indice // 12, indice % 12 + 1


class MotorPredictivoTest(unittest.TestCase):
    def setUp(self) -> None:
        temporal = tempfile.NamedTemporaryFile(suffix=".sqlite", delete=False)
        temporal.close()
        self.db_path = Path(temporal.name)
        conexion = sqlite3.connect(self.db_path)
        try:
            conexion.execute(
                """
                CREATE TABLE conteos_membresia (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    iglesia_id INTEGER NOT NULL,
                    anio INTEGER NOT NULL,
                    mes INTEGER NOT NULL,
                    total_activos INTEGER NOT NULL,
                    estado TEXT NOT NULL,
                    es_sintetico INTEGER NOT NULL DEFAULT 0
                )
                """
            )
            conexion.commit()
        finally:
            conexion.close()

    def tearDown(self) -> None:
        self.db_path.unlink(missing_ok=True)

    def poblar(
        self,
        meses: int = 36,
        sintetico: bool = True,
        omitir: set[tuple[int, int]] | None = None,
    ) -> None:
        omitir = omitir or set()
        filas = []
        for indice_mes in range(meses):
            anio, mes = avanzar_mes(date(2023, 1, 1), indice_mes)
            for iglesia in range(1, 25):
                if (indice_mes, iglesia) in omitir:
                    continue
                filas.append(
                    (
                        iglesia,
                        anio,
                        mes,
                        80 + iglesia + indice_mes,
                        "cerrado",
                        int(sintetico),
                    )
                )
        conexion = sqlite3.connect(self.db_path)
        try:
            conexion.executemany(
                """
                INSERT INTO conteos_membresia
                    (iglesia_id, anio, mes, total_activos, estado, es_sintetico)
                VALUES (?, ?, ?, ?, ?, ?)
                """,
                filas,
            )
            conexion.commit()
        finally:
            conexion.close()

    def test_aplica_protocolo_temporal_y_cuatro_metodos(self) -> None:
        self.poblar()
        serie = holt_prediccion.obtener_serie_distrital(str(self.db_path))

        compuerta = holt_prediccion.verificar_compuerta(serie)
        evaluacion = holt_prediccion.validacion_temporal(serie)

        self.assertTrue(compuerta["aprobada"])
        self.assertFalse(compuerta["aprobada_para_validacion_real"])
        self.assertEqual(
            evaluacion["protocolo"]["numero_origenes_desarrollo"], 7
        )
        self.assertEqual(len(evaluacion["registros_desarrollo"]), 7 * 6 * 4)
        self.assertEqual(len(evaluacion["registros_comprobacion_final"]), 6 * 4)
        self.assertEqual(len(evaluacion["resumen_comprobacion_final"]), 4)
        self.assertEqual(len(evaluacion["resumen_por_horizonte"]), 6 * 4)
        self.assertTrue(evaluacion["evaluacion_completa"])

    def test_datos_sinteticos_solo_generan_prueba_funcional(self) -> None:
        self.poblar(sintetico=True)

        resultado = holt_prediccion.ejecutar_pronostico(str(self.db_path), 6)

        self.assertEqual(resultado["estado"], "evaluado")
        self.assertEqual(resultado["modo_datos"], "prueba_funcional")
        self.assertEqual(len(resultado["pronosticos"]), 6)
        self.assertIn("no validan precision real", resultado["observaciones"])

    def test_rechaza_un_corte_que_no_incluye_las_24_iglesias(self) -> None:
        self.poblar(omitir={(20, 24)})
        serie = holt_prediccion.obtener_serie_distrital(str(self.db_path))

        compuerta = holt_prediccion.verificar_compuerta(serie)

        self.assertFalse(compuerta["aprobada"])
        self.assertIn("24 iglesias", compuerta["motivo"])
        self.assertEqual(
            compuerta["cortes_con_cobertura_incompleta"][0][
                "iglesias_reportadas"
            ],
            23,
        )

    def test_rechaza_discontinuidad_sin_inventar_el_mes_faltante(self) -> None:
        self.poblar(meses=37, omitir={(12, iglesia) for iglesia in range(1, 25)})
        serie = holt_prediccion.obtener_serie_distrital(str(self.db_path))

        compuerta = holt_prediccion.verificar_compuerta(serie)

        self.assertEqual(len(serie), 36)
        self.assertFalse(compuerta["aprobada"])
        self.assertIn("no se imputan", compuerta["motivo"].lower())
        self.assertEqual(compuerta["periodos_faltantes"], ["2024-01"])

    def test_un_fallo_de_holt_se_informa_y_no_se_sustituye(self) -> None:
        self.poblar()
        serie = holt_prediccion.obtener_serie_distrital(str(self.db_path))

        def fallar(_entrenamiento, _horizonte):
            raise RuntimeError("fallo controlado de Holt")

        with patch.dict(
            holt_prediccion.PRONOSTICADORES, {"holt": fallar}, clear=False
        ):
            evaluacion = holt_prediccion.validacion_temporal(serie)

        self.assertFalse(evaluacion["evaluacion_completa"])
        self.assertTrue(
            all(
                registro["metodo"] != "holt"
                for registro in evaluacion["registros_desarrollo"]
            )
        )
        self.assertTrue(
            any(
                error["metodo"] == "holt"
                and "fallo controlado" in error["mensaje"]
                for error in evaluacion["errores_ajuste"]
            )
        )


if __name__ == "__main__":
    unittest.main()
