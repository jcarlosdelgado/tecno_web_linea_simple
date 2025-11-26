<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagoFacilService
{
    private string $apiUrl;
    private string $bearerToken;

    public function __construct()
    {
        $this->apiUrl = config('services.pagofacil.api_url', 'https://masterqr.pagofacil.com.bo/api/services/v2');
        $this->bearerToken = config('services.pagofacil.bearer_token', '');
    }

    /**
     * Genera un código QR para pago
     * 
     * @param array $data Datos de la transacción
     * @return array Respuesta de la API
     */
    public function generateQR(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl . '/generate-qr', $data);

            $responseData = $response->json();

            // Log de la respuesta para debugging
            Log::info('Pago Fácil QR Response', [
                'status' => $response->status(),
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['error']) && $responseData['error'] === 0) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Error desconocido al generar QR',
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('Pago Fácil QR Generation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Error al conectar con Pago Fácil: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consulta el estado de una transacción
     * 
     * @param string|null $pagofacilTransactionId ID de transacción de Pago Fácil
     * @param string|null $companyTransactionId ID de transacción de la empresa
     * @return array Respuesta de la API
     */
    public function queryTransaction(?string $pagofacilTransactionId = null, ?string $companyTransactionId = null): array
    {
        try {
            $data = [];
            
            if ($pagofacilTransactionId) {
                $data['pagofacilTransactionId'] = $pagofacilTransactionId;
            }
            
            if ($companyTransactionId) {
                $data['companyTransactionId'] = $companyTransactionId;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl . '/query-transaction', $data);

            $responseData = $response->json();

            Log::info('Pago Fácil Query Transaction Response', [
                'status' => $response->status(),
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['error']) && $responseData['error'] === 0) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Error al consultar transacción',
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('Pago Fácil Query Transaction Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Error al consultar transacción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Prepara los datos para la generación de QR según el formato de Pago Fácil
     * 
     * @param array $params Parámetros de la transacción
     * @return array Datos formateados
     */
    public function prepareQRData(array $params): array
    {
        return [
            'paymentMethod' => $params['payment_method'] ?? 4,
            'clientName' => $params['client_name'] ?? '',
            'documentType' => $params['document_type'] ?? 1,
            'documentId' => $params['document_id'] ?? '',
            'phoneNumber' => $params['phone_number'] ?? '',
            'email' => $params['email'] ?? '',
            'paymentNumber' => $params['payment_number'] ?? 'PAY-' . time(), // Generar si no existe
            'amount' => $params['amount'] ?? 0,
            'currency' => $params['currency'] ?? 2, // 2 = Bolivianos
            'clientCode' => $params['client_code'] ?? '',
            'callbackUrl' => $params['callback_url'] ?? '',
            'orderDetail' => $params['order_detail'] ?? [
                [
                    'serial' => 1,
                    'product' => $params['product_description'] ?? 'Pago de servicio',
                    'quantity' => 1,
                    'price' => $params['amount'] ?? 0,
                    'discount' => 0,
                    'total' => $params['amount'] ?? 0
                ]
            ]
        ];
    }
}
