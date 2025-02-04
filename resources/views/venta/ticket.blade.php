@extends('layouts.app')

@section('title', 'Ticket de Venta')

@section('content')
<div class="container">
    <h1 class="text-center">Nombre de la Empresa</h1>
    <h2 class="text-center">Ticket de Venta</h2>
    <p><strong>Número de Comprobante:</strong> {{ $venta->numero_comprobante }}</p>
    <p><strong>Fecha y Hora:</strong> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</p>
    <p><strong>Cliente:</strong> {{ $venta->cliente->persona->razon_social }}</p>

    <h3>Productos:</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->productos as $producto)
            <tr>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->pivot->cantidad }}</td>
                <td>{{ number_format($producto->pivot->precio_venta, 2) }}</td>
                <td>{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_venta, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Total: {{ number_format($venta->total, 2) }}</h4>
</div>
@endsection