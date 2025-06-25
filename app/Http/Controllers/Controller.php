<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Mpdf\Mpdf;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * Crear instancia de mPDF con configuración segura
     */
    protected function createSafeMpdf()
    {
        // Crear directorio temp si no existe
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('app/temp'),
            'curlTimeout' => 5,
            'allowedRemoteHosts' => [],
            'useActiveForms' => false,
            'autoScriptToLang' => false,
            'autoLangToFont' => false
        ]);
    }
}
