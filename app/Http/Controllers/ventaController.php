<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Fidelizacion;
use Exception;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ventaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('permission:eliminar-venta', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-diario-venta', ['only' => ['reporteDiario']]);
        $this->middleware('permission:reporte-semanal-venta', ['only' => ['reporteSemanal']]);
        $this->middleware('permission:reporte-mensual-venta', ['only' => ['reporteMensual']]);
        $this->middleware('permission:exportar-reporte-venta', ['only' => ['exportDiario', 'exportSemanal', 'exportMensual']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::with(['comprobante','cliente.persona','user'])
        ->where('estado',1)
        ->latest()
        ->get();

        return view('venta.index',compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subquery = DB::table('compra_producto')
            ->select('producto_id', DB::raw('MAX(created_at) as max_created_at'))
            ->groupBy('producto_id');

        $productos = Producto::join('compra_producto as cpr', function ($join) use ($subquery) {
            $join->on('cpr.producto_id', '=', 'productos.id')
                ->whereIn('cpr.created_at', function ($query) use ($subquery) {
                    $query->select('max_created_at')
                        ->fromSub($subquery, 'subquery')
                        ->whereRaw('subquery.producto_id = cpr.producto_id');
                });
        })
            ->select('productos.nombre', 'productos.id', 'productos.stock', 'cpr.precio_venta')
            ->where('productos.estado', 1)
            ->where('productos.stock', '>', 0)
            ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();
        $comprobantes = Comprobante::all();

        return view('venta.create', compact('productos', 'clientes', 'comprobantes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        try{
            DB::beginTransaction();

            //Obtener el tipo de comprobante
            $comprobante = Comprobante::find($request->comprobante_id);

            //Generar número de comprobante
            $numero_comprobante = Venta::generarNumeroComprobante($request->comprobante_id);

            //Llenar mi tabla venta
            $venta = Venta::create(array_merge($request->validated(), ['numero_comprobante' => $numero_comprobante]));

            //Llenar mi tabla venta_producto
            //1. Recuperar los arrays
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayDescuento = $request->get('arraydescuento');

            //2.Realizar el llenado
            $siseArray = count($arrayProducto_id);
            $cont = 0;

            while($cont < $siseArray){
                $venta->productos()->syncWithoutDetaching([
                    $arrayProducto_id[$cont] => [
                        'cantidad' => $arrayCantidad[$cont],
                        'precio_venta' => $arrayPrecioVenta[$cont],
                        'descuento' => $arrayDescuento[$cont]
                    ]
                ]);

                //Actualizar stock
                $producto = Producto::find($arrayProducto_id[$cont]);
                $stockActual = $producto->stock;
                $cantidad = intval($arrayCantidad[$cont]);

                DB::table('productos')
                ->where('id',$producto->id)
                ->update([
                    'stock' => $stockActual - $cantidad
                ]);

                $cont++;
            }

            // Agregar puntos de fidelización
            $cliente = $venta->cliente;
            $puntos = $venta->total * 0.1; // Ejemplo: 10% del total de la venta en puntos

            if ($cliente->fidelizacion) {
                $cliente->fidelizacion->increment('puntos', $puntos);
            } else {
                Fidelizacion::create([
                    'cliente_id' => $cliente->id,
                    'puntos' => $puntos,
                ]);
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            return redirect()->route('ventas.create')->with('error', 'Error al realizar la venta: ' . $e->getMessage());
        }

        return redirect()->route('ventas.index')->with('success','Venta exitosa');
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        return view('venta.show',compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function reporteDiario()
    {
    $ventas = Venta::whereDate('fecha_hora', now()->toDateString())
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return view('venta.reporte', compact('ventas'))->with('reporte', 'diario');
    }

    public function reporteSemanal()
    {
    $ventas = Venta::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return view('venta.reporte', compact('ventas'))->with('reporte', 'semanal');
    }

    public function reporteMensual()
    {
    $ventas = Venta::whereMonth('fecha_hora', now()->month)
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return view('venta.reporte', compact('ventas'))->with('reporte', 'mensual');
    }

    public function exportDiario()
    {   
    $ventas = Venta::whereDate('fecha_hora', now()->toDateString())
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return Excel::download(new VentasExport($ventas), 'ventas_diarias.xlsx');
    }

    public function exportSemanal()
    {
    $ventas = Venta::whereBetween('fecha_hora', [now()->startOfWeek(), now()->endOfWeek()])
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return Excel::download(new VentasExport($ventas), 'ventas_semanales.xlsx');
    }

    public function exportMensual()
    {
    $ventas = Venta::whereMonth('fecha_hora', now()->month)
        ->with(['comprobante', 'cliente.persona', 'user'])
        ->get();

    return Excel::download(new VentasExport($ventas), 'ventas_mensuales.xlsx');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Venta::where('id',$id)
        ->update([
            'estado' => 0
        ]);

        return redirect()->route('ventas.index')->with('success','Venta eliminada');
    }

    public function ticket(Venta $venta)
    {
        return view('venta.ticket', compact('venta'));
    }

    public function printTicket(Venta $venta)
    {
        try {
            $pdf = Pdf::loadView('venta.ticket_pdf', compact('venta'));
            $fileName = 'ticket_' . $venta->id . '.pdf';
            Storage::put('public/' . $fileName, $pdf->output());

            return Storage::download('public/' . $fileName);
        } catch (Exception $e) {
            return redirect()->route('ventas.show', $venta)->with('error', 'Error al imprimir el ticket: ' . $e->getMessage());
        }
    }
}