@extends('layouts.app')
@section('title', 'Nuevo circuito')
@section('page_title', 'Registrar circuito')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('circuitos.store') }}">@csrf @include('circuitos._form')<div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('circuitos.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-sigem">Guardar circuito</button></div></form></div></div>
@endsection
