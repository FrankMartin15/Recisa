<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>RECISA | Reporte Citas</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333333;
            background-color: #ffffff;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        header {
            border-bottom: 2px solid #00476D;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        #company {
            float: left;
            width: 70%;
        }
        
        #company h2.name {
            color: #00476D;
            font-size: 26px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        #company div {
            color: #555555;
            margin: 5px 0;
            font-size: 14px;
        }
        
        #logo {
            float: right;
            width: 25%;
            text-align: right;
        }
        
        .logo-hospital {
            font-size: 48px;
            color: #00476D;
            line-height: 1;
        }
        
        #details {
            margin-bottom: 30px;
        }
        
        #client {
            float: left;
            width: 60%;
        }
        
        #invoice {
            float: right;
            width: 35%;
            text-align: right;
        }
        
        .to {
            color: #00476D;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        h2.name {
            font-size: 18px;
            color: #333333;
            margin: 8px 0;
            font-weight: 600;
        }
        
        .specialization {
            margin: 5px 0;
            color: #00476D;
            font-weight: 600;
            font-size: 16px;
        }
        
        #invoice h1 {
            color: #00476D;
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .date {
            margin: 5px 0;
            color: #555555;
            font-size: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid #00476D;
        }
        
        th, td {
            border: 1px solid #00476D;
            padding: 10px 8px;
            text-align: center;
            font-size: 12px;
        }
        
        thead th {
            background-color: #ffffff;
            color: #00476D;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #00476D;
        }
        
        tbody td {
            background-color: #ffffff;
            color: #333333;
        }
        
        tbody tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        
        #notices {
            margin-top: 30px;
            padding: 15px;
            border: 2px solid #00476D;
            text-align: center;
            background-color: #ffffff;
        }
        
        #notices > div:first-child {
            color: #00476D;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .notice {
            margin: 8px 0;
            font-size: 12px;
            color: #555555;
        }
        
        .no-appointments {
            text-align: center;
            padding: 30px 20px;
            color: #666666;
            font-style: italic;
            font-size: 14px;
            border: 1px dashed #00476D;
            margin: 20px 0;
        }
        
        .doctor-section {
            page-break-before: auto;
            margin-bottom: 50px;
            border: 1px solid #00476D;
            padding: 20px;
        }
        
        .patient-count {
            color: #00476D;
            padding: 5px 10px;
            border: 1px solid #00476D;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-top: 8px;
        }
        
        .section-title {
            background-color: #ffffff;
            color: #00476D;
            padding: 8px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #00476D;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <header class="clearfix">
        <div id="company">
            <h2 class="name">RECISA</h2>
            <div>JIRON HUAYNA CAPAC S/N, SAUSA, JAUJA</div>
            <div>Centro de Salud Integral</div>
        </div>
        <div id="logo">
            <div class="logo-hospital">⚕</div>
        </div>
    </header>
    
    <main>
        @if(isset($patientDoctor) && $patientDoctor && $patientDoctor->count() > 0)
            @foreach ($patientDoctor as $doctorIndex => $specialization)
                <div class="doctor-section">
                    <div class="section-title">AGENDA MÉDICA DIARIA</div>
                    
                    <div id="details" class="clearfix">
                        <div id="client">
                            <div class="to">Doctor:</div>
                            <h2 class="name">
                                @if($specialization->user)
                                    {{ ($specialization->user->surnames ?? '') . ', ' . ($specialization->user->names ?? '') }}
                                @else
                                    Doctor no disponible
                                @endif
                            </h2>
                            <div class="specialization">
                                @if($specialization->specialization)
                                    {{ $specialization->specialization->name ?? 'Especialidad no disponible' }}
                                @else
                                    Especialidad no disponible
                                @endif
                            </div>
                            
                            @php
                                // Usar datos reales si existen
                                $appointments = $specialization->appointment ?? collect();
                                $pendingAppointments = $appointments->where('status', 0);
                            @endphp
                            
                            @if($pendingAppointments->count() > 0)
                                <div class="patient-count">{{ $pendingAppointments->count() }} Pacientes Programados</div>
                            @endif
                        </div>
                        
                        <div id="invoice">
                            <h1>Reporte Diario</h1>
                            <div class="date">Fecha: {{ date('d/m/Y') }}</div>
                            <div class="date">Horarios: 7:00 A 19:00 HORAS</div>
                        </div>
                    </div>
                    
                    @if($pendingAppointments->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>DNI</th>
                                    <th>Paciente</th>
                                    <th>Número Historial</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAppointments as $index => $appointment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($appointment->patient)
                                                {{ $appointment->patient->dni ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($appointment->patient)
                                                {{ ($appointment->patient->surnames ?? '') . ', ' . ($appointment->patient->names ?? '') }}
                                            @else
                                                Paciente no disponible
                                            @endif
                                        </td>
                                        <td>
                                            @if($appointment->patient)
                                                {{ $appointment->patient->history_number ?? 'Sin asignar' }}
                                            @else
                                                Sin asignar
                                            @endif
                                        </td>
                                        <td>{{ $appointment->time ?? 'Hora no disponible' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-appointments">
                            <p>Este doctor no tiene citas pendientes para hoy.</p>
                        </div>
                    @endif
                </div>
                
                {{-- Salto de página entre doctores (excepto en el último) --}}
                @if($doctorIndex < $patientDoctor->count() - 1)
                    <div style="page-break-after: always;"></div>
                @endif
            @endforeach
        @else
            <div class="no-appointments">
                <p>No hay doctores disponibles para generar el reporte.</p>
            </div>
        @endif
        
        <div id="notices">
            <div>INFORMACIÓN IMPORTANTE</div>
            <div class="notice">Recordar a los doctores reportar sus citas diarias</div>
            <div class="notice">Mantener actualizada la información de contacto de pacientes</div>
            <div class="notice">Reporte generado: {{ date('d/m/Y H:i:s') }}</div>
        </div>
    </main>
</body>
</html>