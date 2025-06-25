#!/bin/bash

echo "🔧 Limpieza final de código residual..."

# Backup final
cp app/Http/Controllers/PatientController.php app/Http/Controllers/PatientController.php.final_backup

# Eliminar líneas específicas que quedaron residuales
sed -i.tmp '/curlFollowLocation.*false/d' app/Http/Controllers/PatientController.php
sed -i.tmp '/CONFIGURACIONES CRÍTICAS PARA EVITAR TIMEOUTS/d' app/Http/Controllers/PatientController.php
sed -i.tmp '/CONFIGURACIONES DE MEMORIA/d' app/Http/Controllers/PatientController.php

# Eliminar líneas vacías duplicadas
sed -i.tmp '/^[[:space:]]*$/N;/^\n$/d' app/Http/Controllers/PatientController.php

echo "✅ Limpieza final completada!"

# Limpiar archivos temporales
rm -f app/Http/Controllers/*.tmp

