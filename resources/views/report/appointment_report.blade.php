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

        .address, .email {
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

        .no-data {
            text-align: center;
            padding: 30px 20px;
            color: #666666;
            font-style: italic;
            font-size: 14px;
            border: 1px dashed #00476D;
            margin: 20px 0;
        }

        .filter {
            margin: 3px 0;
            color: #555555;
            font-size: 12px;
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
    <div id="details" class="clearfix">
        <div id="client">
            <div class="to">REPORTE GENERADO POR:</div>
            <h2 class="name">{{ (Auth::user()->surnames ?? '') . ', ' . (Auth::user()->names ?? '') }}</h2>
            <div class="address">CELULAR: {{ Auth::user()->phone ?? 'N/A' }}</div>
            <div class="email">EMAIL: {{ Auth::user()->email ?? 'N/A' }}</div>

            @if(isset($filters))
                <div class="filter"><strong>Rango:</strong> {{ date('d/m/Y', strtotime($filters['start_date'])) }} - {{ date('d/m/Y', strtotime($filters['end_date'])) }}</div>
                @if(!empty($filters['id_specialization']))
                    <div class="filter"><strong>Especialidad:</strong> {{ $filterSpecialization->name ?? '' }}</div>
                @endif
                @if(!empty($filters['id_doctor']))
                    <div class="filter"><strong>Doctor:</strong> {{ ($filterDoctor->surnames ?? '') . ', ' . ($filterDoctor->names ?? '') }}</div>
                @endif
                @if(!empty($filters['id_patient']))
                    <div class="filter"><strong>Paciente:</strong> {{ ($filterPatient->surnames ?? '') . ', ' . ($filterPatient->names ?? '') }}</div>
                @endif
            @endif
        </div>
        <div id="invoice">
            <h1>LISTADO DE CITAS</h1>
            <div>Total de citas: {{ isset($appointments) ? $appointments->count() : 0 }}</div>
            <div class="date">Fecha: {{ date('d/m/Y') }}</div>
        </div>
    </div>

    @if(isset($appointments) && $appointments && $appointments->count() > 0)
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Paciente</th>
                <th>Doctor</th>
                <th>Especialidad</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($appointments as $index => $appointment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ ($appointment->patient->surnames ?? '') . ', ' . ($appointment->patient->names ?? '') }}</td>
                    <td>{{ (optional($appointment->doctor->user)->surnames ?? '') . ', ' . (optional($appointment->doctor->user)->names ?? '') }}</td>
                    <td>{{ optional($appointment->doctor->specialization)->name ?? 'N/A' }}</td>
                    <td>{{ date('d-m-Y', strtotime($appointment->date)) }}</td>
                    <td>{{ $appointment->time }}</td>
                    <td>
                        @switch($appointment->status)
                            @case(0)
                                Por atender
                                @break
                            @case(1)
                                Atendido
                                @break
                            @case(2)
                                No Asistió
                                @break
                            @default
                                N/A
                        @endswitch
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No hay citas registradas para los filtros seleccionados.</p>
        </div>
    @endif

    <div id="notices">
        <div>INFORMACIÓN IMPORTANTE</div>
        <div class="notice">Este reporte contiene información sensible de las citas del sistema.</div>
        <div class="notice">Reporte generado: {{ date('d/m/Y H:i:s') }}</div>
    </div>
</main>
</body>
</html>
