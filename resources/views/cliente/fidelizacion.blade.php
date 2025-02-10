@extends('layouts.app')

@section('title', 'Puntos de Fidelización')

@section('content')
<div class="container">
    <h1 class="text-center">Puntos de Fidelización</h1>
    <p><strong>Cliente:</strong> {{ $cliente->persona->razon_social }}</p>
    <p><strong>Puntos:</strong> {{ $cliente->fidelizacion->puntos ?? 0 }}</p>
</div>
@endsection