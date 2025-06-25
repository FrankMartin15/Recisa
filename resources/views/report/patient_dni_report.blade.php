<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>RECISA | Reporte Paciente {{ $patient->dni ?? 'N/A' }}</title>
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
        
        .address {
            margin: 5px 0;
            color: #555555;
            font-size: 14px;
        }
        
        #invoice h1 {
            color: #00476D;
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        #invoice div {
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

        /* Clases de estado añadidas para mantener la funcionalidad de color */
        .status-pending { color: #E67E22; font-weight: bold; } /* Naranja */
        .status-completed { color: #27AE60; font-weight: bold; } /* Verde */
        .status-cancelled { color: #C0392B; font-weight: bold; } /* Rojo */

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
        <div id="details" class="clearfix">
            <div id="client">
                <div class="to">Paciente:</div>
                <h2 class="name">{{ ($patient->surnames ?? '') . ', ' . ($patient->names ?? '') }}</h2>
                <div class="address"><strong>DNI:</strong> {{ $patient->dni ?? 'N/A' }}</div>
                <div class="address"><strong>Nº HISTORIAL:</strong> {{ $patient->history_number ?? 'Sin asignar' }}</div>
                <div class="address"><strong>CELULAR:</strong> {{ $patient->phone ?? 'No registrado' }}</div>
                <div class="address"><strong>FECHA NACIMIENTO:</strong> {{ $patient->date ? \Carbon\Carbon::parse($patient->date)->format('d/m/Y') : 'No registrada' }}</div>
            </div>
            <div id="invoice">
                <h1>Historial de Citas</h1>
                <div>Total de citas: {{ $patient->appointments ? $patient->appointments->count() : 0 }}</div>
            </div>
        </div>
        
        @if(isset($patient->appointments) && $patient->appointments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>   
                    @foreach ($patient->appointments as $index => $appointment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($appointment->doctor && $appointment->doctor->user)
                                    {{ ($appointment->doctor->user->surnames ?? '') . ', ' . ($appointment->doctor->user->names ?? '') }}
                                @else
                                    Doctor no disponible
                                @endif
                            </td>
                            <td>
                                @if($appointment->doctor && $appointment->doctor->specialization)
                                    {{ $appointment->doctor->specialization->name ?? 'Especialidad no disponible' }}
                                @else
                                    Especialidad no disponible
                                @endif
                            </td>
                            <td>
                                @if($appointment->date)
                                    {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}
                                @else
                                    Fecha no disponible
                                @endif
                            </td>
                            <td>{{ $appointment->time ?? 'Hora no disponible' }}</td>
                            <td>
                                @switch($appointment->status ?? 0)
                                    @case(0)
                                        <span class="status-pending">Por atender</span>
                                        @break
                                    @case(1)
                                        <span class="status-completed">Atendido</span>
                                        @break
                                    @case(2)
                                        <span class="status-cancelled">No atendido</span>
                                        @break
                                    @default
                                        <span>Estado: {{ $appointment->status }}</span>
                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-appointments">
                <p>Este paciente no tiene citas registradas en el sistema.</p>
            </div>
        @endif
        
        <div id="notices">
            <div>INFORMACIÓN IMPORTANTE</div>
            <div class="notice">Este es un registro confidencial de la historia clínica del paciente.</div>
            <div class="notice">Reporte generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </main>
</body>
</html>