<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class VentasExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function collection()
    {
        return $this->ventas;
    }

    public function map($venta): array
    {
        return [
            $venta->comprobante->tipo_comprobante . ' ' . $venta->numero_comprobante,
            $venta->cliente->persona->razon_social,
            Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i'),
            $venta->user->name,
            $venta->total,
            $venta->comentarios,
            $venta->medio_pago,
            $venta->efectivo,
            $venta->yape,
        ];
    }

    public function headings(): array
    {
        return [
            'Comprobante',
            'Cliente',
            'Fecha y hora',
            'Vendedor',
            'Total',
            'Comentarios',
            'Medio de pago',
            'Efectivo',
            'Yape'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $total = $this->ventas->sum('total');
                $lastRow = $this->ventas->count() + 2; // +2 for the header row and 1-based index

                $event->sheet->appendRows([
                    ['', '', '', '', 'Total: ' . $total, '', '', '', '']
                ], $event);
            },
        ];
    }
}