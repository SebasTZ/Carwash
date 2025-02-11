@extends('layouts.app')

@section('title', 'Reporte de Ventas ' . ucfirst($reporte))

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush
@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .row-not-space {
        width: 110px;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Ventas {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Reporte {{ ucfirst($reporte) }}</li>
    </ol>

    <div class="mb-4">
        <a href="{{ route('ventas.export.' . $reporte) }}">
            <button type="button" class="btn btn-success">Exportar a Excel</button>
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de ventas {{ $reporte }}
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th>Fecha y hora</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Comentarios</th>
                        <th>Medio de pago</th>
                        <th>Efectivo</th>
                        <th>Yape</th>
                        <th>Servicio de lavado</th>
                        <th>Horario de culminación del lavado</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach ($ventas as $item)
                    @php
                        $total += $item->total;
                    @endphp
                    <tr>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->comprobante->tipo_comprobante}}</p>
                            <p class="text-muted mb-0">{{$item->numero_comprobante}}</p>
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">{{ ucfirst($item->cliente->persona->tipo_persona) }}</p>
                            <p class="text-muted mb-0">{{$item->cliente->persona->razon_social}}</p>
                        </td>
                        <td>
                            <div class="row-not-space">
                                <p class="fw-semibold mb-1"><span class="m-1"><i class="fa-solid fa-calendar-days"></i></span>{{\Carbon\Carbon::parse($item->fecha_hora)->format('d-m-Y')}}</p>
                                <p class="fw-semibold mb-0"><span class="m-1"><i class="fa-solid fa-clock"></i></span>{{\Carbon\Carbon::parse($item->fecha_hora)->format('H:i')}}</p>
                            </div>
                        </td>
                        <td>
                            {{$item->user->name}}
                        </td>
                        <td>
                            {{$item->total}}
                        </td>
                        <td>
                            {{$item->comentarios}}
                        </td>
                        <td>
                            {{$item->medio_pago}}
                        </td>
                        <td>
                            {{$item->efectivo}}
                        </td>
                        <td>
                            {{$item->yape}}
                        </td>
                        <td>
                            {{$item->servicio_lavado ? 'Sí' : 'No'}}
                        </td>
                        <td>
                            {{$item->horario_lavado ? \Carbon\Carbon::parse($item->horario_lavado)->format('d-m-Y H:i') : 'N/A'}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                <h4>Total: {{$total}}</h4>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script>
    window.addEventListener('DOMContentLoaded', event => {
        const dataTable = new simpleDatatables.DataTable("#datatablesSimple", {})
    });
</script>
@endpush