@extends('layouts.app')
@section('title', 'Editar iglesia')
@section('page_title', 'Editar iglesia')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('iglesias.update', $iglesia) }}">@csrf @method('PUT') @include('iglesias._form')<div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('iglesias.show', $iglesia) }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-sigem">Actualizar iglesia</button></div></form></div></div>
@endsection
