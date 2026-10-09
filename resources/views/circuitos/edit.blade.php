@extends('layouts.app')
@section('title', 'Editar circuito')
@section('page_title', 'Editar circuito')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('circuitos.update', $circuito) }}">@csrf @method('PUT') @include('circuitos._form')<div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('circuitos.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-sigem">Actualizar circuito</button></div></form></div></div>
@endsection
