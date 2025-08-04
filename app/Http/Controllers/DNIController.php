<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DNIController extends Controller
{
    public function consultarDNI(Request $request)
    {
        // Validar el DNI
        try {
            $request->validate([
                'dni' => 'required|digits:8'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DNI debe tener exactamente 8 dígitos',
                'errors' => $e->errors()
            ], 422);
        }

        $dni = $request->dni;
        Log::info("🔍 Consultando DNI: {$dni}");

        // Probar API 1: apisperu.com (nueva)
        $result = $this->tryApisPeru($dni);
        if ($result['success']) {
            Log::info("✅ DNI encontrado en apisperu.com");
            return response()->json($result);
        }

        // Probar API 2: apis.net.pe (actual)
        $result = $this->tryApisNetPe($dni);
        if ($result['success']) {
            Log::info("✅ DNI encontrado en apis.net.pe");
            return response()->json($result);
        }

        // Ninguna API encontró el DNI - Activar modo manual
        Log::warning("⚠️ DNI {$dni} no encontrado en ninguna API");
        return response()->json([
            'success' => false,
            'not_found' => true,
            'enable_manual' => true,
            'message' => "DNI {$dni} no encontrado. Puede registrar los datos manualmente.",
            'dni' => $dni
        ], 404);
    }

    private function tryApisPeru($dni)
    {
        try {
            $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InRvZ2FrbzY4MzBAN3R1bC5jb20ifQ.-hggTD8LpF4Pgc-boAxMTRYVHKgXZwQ4JEgFP-vVxmQ';
            $startTime = microtime(true);
            
            $response = Http::timeout(8)->get("https://dniruc.apisperu.com/api/v1/dni/{$dni}", [
                'token' => $token
            ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("⏱️ API apisperu.com respondió en {$responseTime}ms");

            if ($response->successful()) {
                $data = $response->json();
                
                // Verificar si encontró datos
                if (isset($data['dni']) && $data['dni'] === $dni && isset($data['nombres'])) {
                    return [
                        'success' => true,
                        'data' => [
                            'numeroDocumento' => $data['dni'],
                            'nombres' => $data['nombres'],
                            'apellidoPaterno' => $data['apellidoPaterno'],
                            'apellidoMaterno' => $data['apellidoMaterno'],
                            'nombreCompleto' => trim($data['apellidoPaterno'] . ' ' . $data['apellidoMaterno'] . ' ' . $data['nombres']),
                            'api_source' => 'apisperu.com',
                            'response_time' => $responseTime . 'ms'
                        ],
                        'message' => 'DNI ENCONTRADO'
                    ];
                }
            }
            
            Log::info("❌ API apisperu.com: DNI no encontrado o respuesta inválida");
            return ['success' => false, 'api' => 'apisperu.com'];
            
        } catch (\Exception $e) {
            Log::error("❌ Error en API apisperu.com: " . $e->getMessage());
            return ['success' => false, 'api' => 'apisperu.com', 'error' => $e->getMessage()];
        }
    }

    private function tryApisNetPe($dni)
    {
        try {
            $token = 'apis-token-17446.THvI8SibU0HHGwOSaI4SeVRO2yw7SFIj';
            $startTime = microtime(true);
            
            $response = Http::timeout(8)->withHeaders([
                'Referer' => 'https://apis.net.pe/consulta-dni-api',
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get('https://api.apis.net.pe/v2/reniec/dni', [
                'numero' => $dni
            ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("⏱️ API apis.net.pe respondió en {$responseTime}ms");

            if ($response->successful()) {
                $data = $response->json();
                
                // Verificar si encontró datos
                if (isset($data['numeroDocumento']) && $data['numeroDocumento'] === $dni && !isset($data['message'])) {
                    return [
                        'success' => true,
                        'data' => [
                            'numeroDocumento' => $data['numeroDocumento'],
                            'nombres' => $data['nombres'],
                            'apellidoPaterno' => $data['apellidoPaterno'],
                            'apellidoMaterno' => $data['apellidoMaterno'],
                            'nombreCompleto' => $data['nombreCompleto'] ?? trim($data['apellidoPaterno'] . ' ' . $data['apellidoMaterno'] . ' ' . $data['nombres']),
                            'api_source' => 'apis.net.pe',
                            'response_time' => $responseTime . 'ms'
                        ],
                        'message' => 'DNI ENCONTRADO'
                    ];
                }
            }
            
            Log::info("❌ API apis.net.pe: DNI no encontrado o respuesta inválida");
            return ['success' => false, 'api' => 'apis.net.pe'];
            
        } catch (\Exception $e) {
            Log::error("❌ Error en API apis.net.pe: " . $e->getMessage());
            return ['success' => false, 'api' => 'apis.net.pe', 'error' => $e->getMessage()];
        }
    }
}
