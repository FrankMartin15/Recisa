#!/bin/bash

echo "🔧 Arreglando configuraciones de mPDF..."

# Backup de archivos
cp app/Http/Controllers/PatientController.php app/Http/Controllers/PatientController.php.backup
cp app/Http/Controllers/AdminController.php app/Http/Controllers/AdminController.php.backup

# PatientController - Reemplazar configuración compleja
sed -i.tmp 's/\$mpdf = new Mpdf(\[/\$mpdf = \$this->createSafeMpdf(); \/\/ OLD: new Mpdf([/g' app/Http/Controllers/PatientController.php

# AdminController - Reemplazar configuración simple  
sed -i.tmp 's/\$mpdf = new Mpdf();/\$mpdf = \$this->createSafeMpdf();/g' app/Http/Controllers/AdminController.php

echo "✅ Archivos actualizados!"
echo "📋 Backups creados: *.backup"

rm -f app/Http/Controllers/*.tmp
