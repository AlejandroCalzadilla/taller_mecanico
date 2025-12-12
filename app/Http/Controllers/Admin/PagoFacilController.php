<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\OrdenTrabajo;
use App\Models\User;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Carbon\Carbon;

class PagoFacilController extends Controller
{
    /**
     * Estados de pago según PagoFácil
     */
    private const PAYMENT_STATUS_PENDING = 0;
    private const PAYMENT_STATUS_COMPLETED = 2;
    private const PAYMENT_STATUS_REJECTED = 3;
    private const PAYMENT_STATUS_SUCCESS = 5;

    /**
     * Mostrar página para generar QR de pago
     */
    public function index(Request $request)
    {
        $pagoId = $request->query('pago_id');
        if (!$pagoId) {
            return redirect()->route('admin.pagos.index')->with('error', 'ID de pago requerido.');
        }

        $pago = Pago::with([
            'ordenTrabajo.diagnostico.cita.cliente',
            'ordenTrabajo.diagnostico.cita.vehiculo',
            'detalles'
        ])->findOrFail($pagoId);

        return Inertia::render('Admin/Pagos/GenerarQR', [
            'pago' => $pago,
            'metodosPago' => PagoDetalle::getMetodosPagoDisponibles(),
        ]);
    }

    /**
     * Generar QR para pago de orden de trabajo
     */
    public function generarQR(Request $request)
    {
        try {
            Log::info('=== INICIANDO GENERACIÓN DE QR ===', ['request' => $request->all()]);

            $request->validate([
                'pago_id' => 'required|exists:pagos,id',
                'monto' => 'required|numeric|min:0.01',
            ]);

            // Obtener el pago
            $pago = Pago::with([
                'ordenTrabajo.diagnostico.cita.cliente',
                'ordenTrabajo.diagnostico.cita.vehiculo',
                'ordenTrabajo.mecanico'
            ])->findOrFail($request->pago_id);

            // Validar que el usuario sea el dueño del pago (cliente autenticado)
            $usuarioAutenticado = auth()->user();
            $clientePago = $pago->ordenTrabajo->diagnostico->cita->cliente;
            
            // Si el usuario es cliente, debe ser el dueño del pago
            if ($usuarioAutenticado->tipo === 'cliente' && $usuarioAutenticado->id !== $clientePago->id) {
                Log::warning('Intento de acceso no autorizado a generar QR', [
                    'usuario_id' => $usuarioAutenticado->id,
                    'cliente_pago_id' => $clientePago->id,
                    'pago_id' => $pago->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para generar QR para este pago'
                ], 403);
            }

            Log::info('Pago encontrado', [
                'pago_id' => $pago->id,
                'codigo' => $pago->codigo,
                'monto_total' => $pago->monto_total
            ]);

            // Validar que el pago esté en estado pendiente
            if ($pago->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pago ya ha sido procesado'
                ], 400);
            }

            // Obtener token de PagoFácil
            $tokenResponse = $this->obtenerToken();
            if (!isset($tokenResponse['values']['accessToken'])) {
                Log::error('No se pudo obtener token', ['response' => $tokenResponse]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error de autenticación con PagoFácil'
                ], 500);
            }

            $accessToken = $tokenResponse['values']['accessToken'];
            Log::info('Token obtenido correctamente');

            // Preparar detalles del pago
            $detalles = $this->formatearDetallesOrden($pago);
            $nroPago = $pago->codigo . '-' . time();

            $body = [
                "paymentMethod" => 4, // QR
                "clientName" => $pago->ordenTrabajo->diagnostico->cita->cliente->nombre,
                "documentType" => 1,
                "documentId" => "0",
                "phoneNumber" => $pago->ordenTrabajo->diagnostico->cita->cliente->telefono ?? "0",
                "email" => $pago->ordenTrabajo->diagnostico->cita->cliente->email ?? "info@taller.com",
                "paymentNumber" => $nroPago,
                "amount" => (float) $request->monto,
                "currency" => 2, // BOB
                "clientCode" => (string) $pago->ordenTrabajo->diagnostico->cita->cliente->id,
                "callbackUrl" => config('pagofacil.callback_url'),
                "orderDetail" => $detalles,
            ];

            Log::info('Body de solicitud preparado', ['body' => $body]);

            // Enviar solicitud a PagoFácil
            $client = new Client();
            $url = config('pagofacil.base_url') . '/generate-qr';

            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => $body,
                'timeout' => 30
            ]);

            $responseContent = $response->getBody()->getContents();
            $result = json_decode($responseContent, true);

            Log::info('Respuesta de PagoFácil recibida', ['result' => $result]);

            if (!isset($result['values'])) {
                Log::error('Respuesta inesperada de PagoFácil', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar QR'
                ], 500);
            }

            $values = $result['values'];
            $qrBase64 = $values['qrBase64'] ?? null;
            $transactionId = $values['transactionId'] ?? null;

            if (!$qrBase64 || !$transactionId) {
                Log::error('Datos QR incompletos', ['values' => $values]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener datos del QR'
                ], 500);
            }

            Log::info('QR generado correctamente', [
                'transaction_id' => $transactionId,
                'pago_id' => $pago->id
            ]);

            return response()->json([
                'success' => true,
                'qr_image' => "data:image/png;base64," . $qrBase64,
                'transaction_id' => $transactionId,
                'nro_pago' => $nroPago,
                'pago_id' => $pago->id
            ]);

        } catch (\Throwable $th) {
            Log::error('Error en generarQR', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Consultar estado de un pago en PagoFácil
     */
    public function consultarEstado(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');

            if (!$transactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID es requerido'
                ], 400);
            }

            // Obtener token
            $tokenResponse = $this->obtenerToken();
            if (!isset($tokenResponse['values']['accessToken'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de autenticación'
                ], 500);
            }

            $accessToken = $tokenResponse['values']['accessToken'];

            $client = new Client();
            $response = $client->post(config('pagofacil.base_url') . '/query-transaction', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => [
                    'pagofacilTransactionId' => $transactionId
                ],
                'http_errors' => false,
                'timeout' => 90,
                'connect_timeout' => 10
            ]);

            $responseContent = $response->getBody()->getContents();
            $result = json_decode($responseContent, true);

            Log::info('Consulta de estado realizada', [
                'transaction_id' => $transactionId,
                'result' => $result
            ]);

            if (!isset($result['values'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos no encontrados'
                ], 404);
            }

            $values = $result['values'];

            return response()->json([
                'success' => true,
                'data' => [
                    'pagofacilTransactionId' => $values['pagofacilTransactionId'] ?? null,
                    'paymentStatus' => $values['paymentStatus'] ?? null,
                    'paymentDate' => $values['paymentDate'] ?? null,
                    'paymentTime' => $values['paymentTime'] ?? null,
                    'paymentStatusDescription' => $values['paymentStatusDescription'] ?? ''
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en consultarEstado', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar estado'
            ], 500);
        }
    }

    /**
     * Callback para notificaciones de PagoFácil
     * Ruta pública: /api/pagos/callback
     */
    public function callback(Request $request)
    {
        try {
            Log::info('=== CALLBACK PAGOFÁCIL RECIBIDO ===', ['data' => $request->all()]);

            $pedidoId = $request->input('PedidoID');
            $estado = $request->input('Estado');
            $metodoPago = $request->input('MetodoPago');
            $fecha = $request->input('Fecha');
            $hora = $request->input('Hora');

            if (!$pedidoId) {
                Log::error('Callback sin PedidoID', ['data' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "PedidoID es requerido",
                    'values' => false
                ]);
            }

            // Buscar pago por el código en las notas
            $pago = Pago::whereRaw("notas LIKE ?", ["%{$pedidoId}%"])->first();

            if (!$pago) {
                Log::error('Pago no encontrado', [
                    'pedido_id' => $pedidoId,
                    'callback_data' => $request->all()
                ]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "Pago no encontrado",
                    'values' => false
                ]);
            }

            // Mapear estado de PagoFácil
            $estadoInterno = $this->mapearEstadoPago($estado);

            Log::info('Estado mapeado', [
                'estado_pagofacil' => $estado,
                'estado_interno' => $estadoInterno,
                'pago_id' => $pago->id
            ]);

            // Actualizar el pago
            $pago->update([
                'estado' => $estadoInterno,
                'notas' => $pago->notas . " | Confirmado: {$fecha} {$hora} | Método: {$metodoPago}"
            ]);

            // Si se pagó, actualizar la orden de trabajo
            if ($estadoInterno === 'pagado_total') {
                $orden = $pago->ordenTrabajo;
                if ($orden && $orden->estado === 'completada') {
                    $orden->update(['estado' => 'entregada']);
                    Log::info('Orden actualizada a entregada', ['orden_id' => $orden->id]);
                }
            }

            Log::info('Pago actualizado exitosamente', [
                'pago_id' => $pago->id,
                'estado_nuevo' => $estadoInterno
            ]);

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "Pago procesado correctamente",
                'values' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error en callback', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => "Error procesando el pago",
                'values' => false
            ]);
        }
    }

    /**
     * Página de retorno después del pago
     */
    public function return(Request $request)
    {
        $status = $request->query('status', 'pending');
        $pagoId = $request->query('pago_id');
        $message = '';

        if ($status === 'success') {
            $message = 'Pago completado exitosamente.';
        } elseif ($status === 'error') {
            $message = 'Hubo un error con el pago. Intenta nuevamente.';
        } else {
            $message = 'Pago pendiente de confirmación.';
        }

        return Inertia::render('Admin/Pagos/PaymentReturn', [
            'status' => $status,
            'message' => $message,
            'pago_id' => $pagoId,
            'redirect_url' => route('admin.pagos.index')
        ]);
    }

    /**
     * Obtener token de PagoFácil
     */
    private function obtenerToken()
    {
        try {
            $client = new Client();

            $response = $client->post(config('pagofacil.base_url') . '/login', [
                'headers' => [
                    'Accept' => 'application/json',
                    'tcTokenService' => config('pagofacil.token_service'),
                    'tcTokenSecret' => config('pagofacil.token_secret')
                ],
                'timeout' => 30
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Token obtenido de PagoFácil', ['response' => $result]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Error al obtener token', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw new \Exception("Error al obtener token: " . $e->getMessage());
        }
    }

    /**
     * Formatear detalles de la orden para PagoFácil
     */
    private function formatearDetallesOrden($pago)
    {
        $orden = $pago->ordenTrabajo;
        $cliente = $orden->diagnostico->cita->cliente;
        $vehiculo = $orden->diagnostico->cita->vehiculo;

        return [
            [
                'serial' => 1,
                'product' => "Orden: {$orden->codigo} - {$vehiculo->marca} {$vehiculo->modelo}",
                'quantity' => 1,
                'price' => (float) $orden->costo_mano_obra,
                'discount' => 0,
                'total' => (float) $orden->costo_mano_obra
            ],
            [
                'serial' => 2,
                'product' => 'Repuestos',
                'quantity' => 1,
                'price' => (float) $orden->costo_repuestos,
                'discount' => 0,
                'total' => (float) $orden->costo_repuestos
            ],
            [
                'serial' => 3,
                'product' => "Cliente: {$cliente->nombre} | Placa: {$vehiculo->placa}",
                'quantity' => 1,
                'price' => 0,
                'discount' => 0,
                'total' => 0
            ]
        ];
    }

    /**
     * Mapear estado de PagoFácil a estado interno
     */
    private function mapearEstadoPago($estado)
    {
        $estadoLower = strtolower((string) $estado);

        // Estados completados
        if (
            $estadoLower === 'completado' ||
            $estadoLower === 'pagado' ||
            $estado === '5' ||
            $estado === 5 ||
            $estado === self::PAYMENT_STATUS_SUCCESS
        ) {
            return 'pagado_total';
        }

        // Estados rechazados
        if (
            $estadoLower === 'rechazado' ||
            $estadoLower === 'cancelado' ||
            $estado === '3' ||
            $estado === 3 ||
            $estado === self::PAYMENT_STATUS_REJECTED
        ) {
            return 'cancelado';
        }

        return 'pendiente';
    }

    /**
     * Obtener estado actual de un pago
     */
    public function obtenerEstadoPago(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');

            if (!$transactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID es requerida'
                ], 400);
            }

            // Buscar el pago
            $pago = Pago::whereRaw("notas LIKE ?", ["%TransactionID: {$transactionId}%"])->first();

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            // Consultar estado en PagoFácil
            $estadoPagoFacil = null;
            try {
                $tokenResponse = $this->obtenerToken();
                $accessToken = $tokenResponse['values']['accessToken'] ?? null;

                if ($accessToken) {
                    $client = new Client();
                    $response = $client->post(config('pagofacil.base_url') . '/query-transaction', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $accessToken
                        ],
                        'json' => [
                            'pagofacilTransactionId' => (int) $transactionId
                        ],
                        'http_errors' => false,
                        'timeout' => 90
                    ]);

                    $result = json_decode($response->getBody()->getContents(), true);
                    $estadoPagoFacil = $result['values'] ?? null;

                    // Actualizar si cambió a pagado
                    if ($estadoPagoFacil && ($estadoPagoFacil['paymentStatus'] === 5 || $estadoPagoFacil['paymentStatus'] === '5')) {
                        if ($pago->estado !== 'pagado_total') {
                            $pago->update(['estado' => 'pagado_total']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error consultando PagoFácil', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'pago' => [
                    'id' => $pago->id,
                    'codigo' => $pago->codigo,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto_total,
                    'tipo_pago' => $pago->tipo_pago,
                    'fecha' => $pago->created_at
                ],
                'estado_pagofacil' => $estadoPagoFacil
            ]);

        } catch (\Exception $e) {
            Log::error('Error en obtenerEstadoPago', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno'
            ], 500);
        }
    }
}
