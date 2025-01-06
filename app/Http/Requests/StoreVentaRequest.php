<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fecha_hora' => 'required',
            'impuesto' => 'required',
            'total' => 'required|numeric',
            'cliente_id' => 'required|exists:clientes,id',
            'user_id' => 'required|exists:users,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'comentarios' => 'nullable|string',
            'medio_pago' => 'required|string',
            'efectivo' => 'nullable|numeric',
            'yape' => 'nullable|numeric'
        ];
    }
}