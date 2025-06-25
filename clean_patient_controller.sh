#!/bin/bash

echo "🧹 Limpiando código residual en PatientController..."

# Backup adicional
cp app/Http/Controllers/PatientController.php app/Http/Controllers/PatientController.php.backup3

# Eliminar líneas residuales después de createSafeMpdf()
perl -i -pe '
    # Si encontramos createSafeMpdf, marcar que empezamos a limpiar
    if (/\$mpdf = \$this->createSafeMpdf\(\)/) {
        $cleanup_mode = 1;
        $skip_next = 0;
    } 
    # Si estamos en modo limpieza y vemos líneas que parecen configuración vieja
    elsif ($cleanup_mode && (/mode.*utf-8|format.*A4|tempDir|curlTimeout|allowedRemoteHosts|useActiveForms|default_font|fontDir|fontdata|\]\);/)) {
        $_ = ""; # Eliminar esta línea
        # Si vemos ]);, terminamos el modo limpieza
        if (/\]\);/) {
            $cleanup_mode = 0;
        }
    }
    # Si encontramos una línea que claramente no es parte de la config vieja, salir del modo limpieza
    elsif ($cleanup_mode && !/^\s*(\/\/|$)/ && !/mode|format|temp|curl|font|allowed/) {
        $cleanup_mode = 0;
    }
' app/Http/Controllers/PatientController.php

echo "✅ PatientController limpiado!"
