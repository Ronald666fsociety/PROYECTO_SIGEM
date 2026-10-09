@extends('layouts.app')
@section('title', 'Nueva iglesia')
@section('page_title', 'Registrar iglesia')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('iglesias.store') }}">@csrf @include('iglesias._form')<div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('iglesias.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-sigem">Guardar iglesia</button></div></form></div></div>
@endsection
