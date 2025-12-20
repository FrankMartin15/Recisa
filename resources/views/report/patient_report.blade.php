<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>RECISA | Reporte de Pacientes</title>
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
            font-size: 13px;
        }

        .email {
            margin: 5px 0;
            color: #00476D;
            font-size: 13px;
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

        th,
        td {
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

        #notices>div:first-child {
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

        .section-title {
            background-color: #ffffff;
            color: #00476D;
            padding: 8px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #00476D;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
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

        .no-patients {
            text-align: center;
            padding: 30px 20px;
            color: #666666;
            font-style: italic;
            font-size: 14px;
            border: 1px dashed #00476D;
            margin: 20px 0;
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
        <div class="section-title">REGISTRO GENERAL DE PACIENTES</div>

        <div id="details" class="clearfix">
            <div id="client">
                <div class="to">Generado por:</div>
                <h2 class="name">Sistema RECISA</h2>
                <div class="address">Base de datos de pacientes registrados</div>
                <div class="patient-count">{{ count($patients) }} Pacientes Registrados</div>
            </div>
            <div id="invoice">
                <h1>PACIENTES</h1>
                <div class="date">Fecha: {{ date('d/m/Y') }}</div>
                <div class="date">Horarios: 7:00 A 19:00 HORAS</div>
                <div class="date">Estado: Activos</div>
            </div>
        </div>

        @if (isset($patients) && count($patients) > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Paciente</th>
                        <th>DNI</th>
                        <th>Celular</th>
                        <th>Fecha Nacimiento</th>
                        <th>Nº Historial</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patients as $index => $patient)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ ($patient->surnames ?? '') . ', ' . ($patient->names ?? '') }}</td>
                            <td>{{ $patient->dni ?? 'N/A' }}</td>
                            <td>{{ $patient->phone ?? 'No registrado' }}</td>
                            <td>
                                @if ($patient->age)
                                    {{ \Carbon\Carbon::parse($patient->age)->format('d/m/Y') }}
                                @else
                                    No registrada
                                @endif
                            </td>
                            <td>{{ $patient->history_number ?? 'Sin asignar' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-patients">
                <p>No hay pacientes registrados en el sistema.</p>
            </div>
        @endif

        <div id="notices">
            <div>INFORMACIÓN IMPORTANTE</div>
            <div class="notice">Reporte diario de pacientes registrados en el sistema</div>
            <div class="notice">Reporte generado: {{ date('d/m/Y H:i:s') }}</div>
        </div>
    </main>
</body>

</html>
