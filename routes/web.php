<?php

use App\Http\Controllers\categoriaController;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\compraController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\marcaController;
use App\Http\Controllers\presentacioneController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\proveedorController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\ventaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/',[homeController::class,'index'])->name('panel');

Route::resources([
    'categorias' => categoriaController::class,
    'presentaciones' => presentacioneController::class,
    'marcas' => marcaController::class,
    'productos' => ProductoController::class,
    'clientes' => clienteController::class,
    'proveedores' => proveedorController::class,
    'compras' => compraController::class,
    'ventas' => ventaController::class,
    'users' => userController::class,
    'roles' => roleController::class,
    'profile' => profileController::class
]);

Route::get('/login',[loginController::class,'index'])->name('login');
Route::post('/login',[loginController::class,'login']);
Route::get('/logout',[logoutController::class,'logout'])->name('logout');

Route::get('ventas/reporte/diario', [VentaController::class, 'reporteDiario'])->name('ventas.reporte.diario');
Route::get('ventas/reporte/semanal', [VentaController::class, 'reporteSemanal'])->name('ventas.reporte.semanal');
Route::get('ventas/reporte/mensual', [VentaController::class, 'reporteMensual'])->name('ventas.reporte.mensual');

Route::get('ventas/export/diario', [VentaController::class, 'exportDiario'])->name('ventas.export.diario');
Route::get('ventas/export/semanal', [VentaController::class, 'exportSemanal'])->name('ventas.export.semanal');
Route::get('ventas/export/mensual', [VentaController::class, 'exportMensual'])->name('ventas.export.mensual');

Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
Route::get('ventas/{venta}/print-ticket', [VentaController::class, 'printTicket'])->name('ventas.printTicket');
Route::get('clientes/{cliente}/fidelizacion', [ClienteController::class, 'fidelizacion'])->name('clientes.fidelizacion');

Route::get('/401', function () {
    return view('pages.401');
});
Route::get('/404', function () {
    return view('pages.404');
});
Route::get('/500', function () {
    return view('pages.500');
});
