<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar con Roles y Módulo Activo</title>
    {{-- La dependencia de Bootstrap (JS) es necesaria para los menús desplegables --}}
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> --}}
    
    {{-- =================================================================
         ESTILOS PARA EL INDICADOR DE MÓDULO ACTIVO (DEL CÓDIGO 1)
         ================================================================= --}}
    <style>
        /* Indicador sutil para sección activa */
        .sidebar-item.active > .sidebar-link {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #007bff;
        }

        /* Estilo para sub-items activos dentro de un dropdown */
        .sidebar-dropdown .sidebar-item.active .sidebar-link, /* Ajustado para ser genérico */
        .sidebar-dropdown .sidebar-item1.active .sidebar-link,
        .sidebar-dropdown .sidebar-item2.active .sidebar-link {
            background-color: rgba(255, 255, 255, 0.08);
            border-left: 2px solid #28a745;
            margin-left: 15px; /* Mantiene la indentación */
        }

        /* Mantener estilos originales */
        .sidebar-link {
            color: #ffffff;
            text-decoration: none;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.3s ease;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
    </style>
</head>
<body>

{{-- =================================================================
     ESTRUCTURA HTML CON LÓGICA DE ROLES DE BLADE (DEL CÓDIGO 2)
     ================================================================= --}}
<aside id="sidebar" style="height: auto; min-height: 100vh;">
    <div class="d-flex">
        <button class="toggle-btn" type="button">
            <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                <path fill="#ffffff" d="M192 48c0-26.5 21.5-48 48-48L400 0c26.5 0 48 21.5 48 48l0 464-80 0 0-80c0-26.5-21.5-48-48-48s-48 21.5-48 48l0 80-80 0 0-464zM48 96l112 0 0 416L48 512c-26.5 0-48-21.5-48-48L0 320l80 0c8.8 0 16-7.2 16-16s-7.2-16-16-16L0 288l0-64 80 0c8.8 0 16-7.2 16-16s-7.2-16-16-16L0 192l0-48c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48l0 48-80 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l80 0 0 64-80 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l80 0 0 144c0 26.5-21.5 48-48 48l-112 0 0-416 112 0zM312 64c-8.8 0-16 7.2-16 16l0 24-24 0c-8.8 0-16 7.2-16 16l0 16c0 8.8 7.2 16 16 16l24 0 0 24c0 8.8 7.2 16 16 16l16 0c8.8 0 16-7.2 16-16l0-24 24 0c8.8 0 16-7.2 16-16l0-16c0-8.8-7.2-16-16-16l-24 0 0-24c0-8.8-7.2-16-16-16l-16 0z" />
            </svg>
        </button>
        <div class="sidebar-logo">
            <a href="">RECISA</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        {{-- EL SCRIPT AÑADIRÁ 'active' Y 'show' DINÁMICAMENTE. NO SE NECESITAN AQUÍ. --}}
        @switch(Auth::user()->user_level)
        
        {{-- ================== ROL 1: ADMINISTRADOR ================== --}}
        @case(1)
            <li class="sidebar-item">
                <a href="{{url('admin/dashboard')}}" class="sidebar-link">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ffffff" d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" /></svg>
                    <span>Dashboad</span>
                </a>
            </li>
            <li class="sidebar-item offline-hide">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth1" aria-expanded="false" aria-controls="auth1">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#ffffff" d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-96 55.2C54 332.9 0 401.3 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7c0-81-54-149.4-128-171.1l0 50.8c27.6 7.1 48 32.2 48 62l0 40c0 8.8-7.2 16-16 16l-16 0c-8.8 0-16-7.2-16-16s7.2-16 16-16l0-24c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 24c8.8 0 16 7.2 16 16s-7.2 16-16 16l-16 0c-8.8 0-16-7.2-16-16l0-40c0-29.8 20.4-54.9 48-62l0-57.1c-6-.6-12.1-.9-18.3-.9l-91.4 0c-6.2 0-12.3 .3-18.3 .9l0 65.4c23.1 6.9 40 28.3 40 53.7c0 30.9-25.1 56-56 56s-56-25.1-56-56c0-25.4 16.9-46.8 40-53.7l0-59.1zM144 448a24 24 0 1 0 0-48 24 24 0 1 0 0 48z" /></svg>
                    <span>Gestión de Personal</span>
                </a>
                <ul id="auth1" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item1"><a href="{{url('admin/admin/add')}}" class="sidebar-link">Registrar Personal</a></li>
                    <li class="sidebar-item1"><a href="{{url('admin/admin/list')}}" class="sidebar-link">Listar Personal</a></li>
                </ul>
            </li>
            <li class="sidebar-item offline-hide">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth2" aria-expanded="false" aria-controls="auth2">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="#ffffff" d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM609.3 512l-137.8 0c5.4-9.4 8.6-20.3 8.6-32l0-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2l61.4 0C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" /></svg>
                    <span>Gestión Roles</span>
                </a>
                <ul id="auth2" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('admin/rol/add')}}" class="sidebar-link">Registrar Rol</a></li>
                    <li class="sidebar-item2"><a href="{{url('admin/rol/list')}}" class="sidebar-link">Listar Rol</a></li>
                </ul>
            </li>
            <li class="sidebar-item offline-hide">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth3" aria-expanded="false" aria-controls="auth3">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="#ffffff" d="M160 64c0-35.3 28.7-64 64-64L576 0c35.3 0 64 28.7 64 64l0 288c0 35.3-28.7 64-64 64l-239.2 0c-11.8-25.5-29.9-47.5-52.4-64l99.6 0 0-32c0-17.7 14.3-32 32-32l64 0c17.7 0 32 14.3 32 32l0 32 64 0 0-288L224 64l0 49.1C205.2 102.2 183.3 96 160 96l0-32zm0 64a96 96 0 1 1 0 192 96 96 0 1 1 0-192zM133.3 352l53.3 0C260.3 352 320 411.7 320 485.3c0 14.7-11.9 26.7-26.7 26.7L26.7 512C11.9 512 0 500.1 0 485.3C0 411.7 59.7 352 133.3 352z" /></svg>
                    <span>Gestión Especialidad</span>
                </a>
                <ul id="auth3" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('admin/specialization')}}" class="sidebar-link">Registrar Especialidad</a></li>
                    <li class="sidebar-item2"><a href="{{url('admin/assignment')}}" class="sidebar-link">Asignar Especialidades</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth5" aria-expanded="false" aria-controls="auth5">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ffffff" d="M48 0C21.5 0 0 21.5 0 48L0 256l144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L0 288l0 64 144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L0 384l0 80c0 26.5 21.5 48 48 48l217.9 0c-6.3-10.2-9.9-22.2-9.9-35.1c0-46.9 25.8-87.8 64-109.2l0-95.9L320 48c0-26.5-21.5-48-48-48L48 0zM152 64l16 0c8.8 0 16 7.2 16 16l0 24 24 0c8.8 0 16 7.2 16 16l0 16c0 8.8-7.2 16-16 16l-24 0 0 24c0 8.8-7.2 16-16 16l-16 0c-8.8 0-16-7.2-16-16l0-24-24 0c-8.8 0-16-7.2-16-16l0-16c0-8.8 7.2-16 16-16l24 0 0-24c0-8.8 7.2-16 16-16zM512 272a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM288 477.1c0 19.3 15.6 34.9 34.9 34.9l218.2 0c19.3 0 34.9-15.6 34.9-34.9c0-51.4-41.7-93.1-93.1-93.1l-101.8 0c-51.4 0-93.1 41.7-93.1 93.1z" /></svg>
                    <span>Gestión de Pacientes</span>
                </a>
                <ul id="auth5" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('recisa/patients/add')}}" class="sidebar-link">Registrar Paciente</a></li>
                    <li class="sidebar-item2"><a href="{{url('recisa/patients/list')}}" class="sidebar-link">Listar Pacientes</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth4" aria-expanded="false" aria-controls="auth4">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#ffffff" d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z" /></svg>
                    <span>Gestión de citas</span>
                </a>
                <ul id="auth4" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('recisa/appoitnment/add')}}" class="sidebar-link">Registrar Cita</a></li>
                    <li class="sidebar-item2"><a href="{{url('recisa/appoitnment/list')}}" class="sidebar-link">Listar Citas</a></li>
                </ul>
            </li>
            @break

        {{-- ================== ROL 2: SECRETARIA ================== --}}
        @case(2)
            <li class="sidebar-item">
                <a href="{{url('secretary/dashboard')}}" class="sidebar-link">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ffffff" d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" /></svg>
                    <span>Dashboad</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth6" aria-expanded="false" aria-controls="auth6">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="#ffffff" d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-288-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zM155.7 250.2L192 302.1l36.3-51.9c7.6-10.9 22.6-13.5 33.4-5.9s13.5 22.6 5.9 33.4L221.3 344l46.4 66.2c7.6 10.9 5 25.8-5.9 33.4s-25.8 5-33.4-5.9L192 385.8l-36.3 51.9c-7.6 10.9-22.6 13.5-33.4 5.9s-13.5-22.6-5.9-33.4L162.7 344l-46.4-66.2c-7.6-10.9-5-25.8 5.9-33.4s25.8-5 33.4 5.9z" /></svg>
                    <span>Gestión de citas</span>
                </a>
                <ul id="auth6" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('secretary/reporte/cita')}}" class="sidebar-link">Reporte de Citas</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth5" aria-expanded="false" aria-controls="auth5">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ffffff" d="M48 0C21.5 0 0 21.5 0 48L0 256l144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L0 288l0 64 144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L0 384l0 80c0 26.5 21.5 48 48 48l217.9 0c-6.3-10.2-9.9-22.2-9.9-35.1c0-46.9 25.8-87.8 64-109.2l0-95.9L320 48c0-26.5-21.5-48-48-48L48 0zM152 64l16 0c8.8 0 16 7.2 16 16l0 24 24 0c8.8 0 16 7.2 16 16l0 16c0 8.8-7.2 16-16 16l-24 0 0 24c0 8.8-7.2 16-16 16l-16 0c-8.8 0-16-7.2-16-16l0-24-24 0c-8.8 0-16-7.2-16-16l0-16c0-8.8 7.2-16 16-16l24 0 0-24c0-8.8 7.2-16 16-16zM512 272a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM288 477.1c0 19.3 15.6 34.9 34.9 34.9l218.2 0c19.3 0 34.9-15.6 34.9-34.9c0-51.4-41.7-93.1-93.1-93.1l-101.8 0c-51.4 0-93.1 41.7-93.1 93.1z" /></svg>
                    <span>Gestión de Pacientes</span>
                </a>
                <ul id="auth5" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('recisa/patients/add')}}" class="sidebar-link">Registrar Paciente</a></li>
                    <li class="sidebar-item2"><a href="{{url('recisa/patients/list')}}" class="sidebar-link">Listar Pacientes</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth4" aria-expanded="false" aria-controls="auth4">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#ffffff" d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z" /></svg>
                    <span>Gestión de citas</span>
                </a>
                <ul id="auth4" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item2"><a href="{{url('recisa/appoitnment/add')}}" class="sidebar-link">Registrar Cita</a></li>
                    <li class="sidebar-item2"><a href="{{url('recisa/appoitnment/list')}}" class="sidebar-link">Listar Citas</a></li>
                </ul>
            </li>
            @break

        {{-- ================== ROL 3: DOCTOR ================== --}}
        @case(3)
            <li class="sidebar-item">
                <a href="{{url('doctor/dashboard')}}" class="sidebar-link">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ffffff" d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" /></svg>
                    <span>Dashboad</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{url('doctor/citas/list')}}" class="sidebar-link">
                    <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="#ffffff" d="M160 64c0-35.3 28.7-64 64-64L576 0c35.3 0 64 28.7 64 64l0 288c0 35.3-28.7 64-64 64l-239.2 0c-11.8-25.5-29.9-47.5-52.4-64l99.6 0 0-32c0-17.7 14.3-32 32-32l64 0c17.7 0 32 14.3 32 32l0 32 64 0 0-288L224 64l0 49.1C205.2 102.2 183.3 96 160 96l0-32zm0 64a96 96 0 1 1 0 192 96 96 0 1 1 0-192zM133.3 352l53.3 0C260.3 352 320 411.7 320 485.3c0 14.7-11.9 26.7-26.7 26.7L26.7 512C11.9 512 0 500.1 0 485.3C0 411.7 59.7 352 133.3 352z" /></svg>
                    <span>Mis Citas</span>
                </a>
            </li>
            @break

        @default
        @endswitch
        
        <br><br><br><br>
        <div class="d-flex">
            <button class="toggle-btn" type="button">
                <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="#ffffff" d="M32 64c17.7 0 32 14.3 32 32l0 320c0 17.7-14.3 32-32 32s-32-14.3-32-32L0 96C0 78.3 14.3 64 32 64zm214.6 73.4c12.5 12.5 12.5 32.8 0 45.3L205.3 224l229.5 0-41.4-41.4c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l96 96c12.5 12.5 12.5 32.8 0 45.3l-96 96c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L434.7 288l-229.5 0 41.4 41.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0l-96-96c-12.5-12.5-12.5-32.8 0-45.3l96-96c12.5-12.5 32.8-12.5 45.3 0zM640 96l0 320c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-320c0-17.7 14.3-32 32-32s32 14.3 32 32z" /></svg>
            </button>
        </div>
    </ul>
    <div class="sidebar-footer">
        <a href="{{url('logout')}}" class="sidebar-link">
            <svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#ffffff" d="M320 32c0-9.9-4.5-19.2-12.3-25.2S289.8-1.4 280.2 1l-179.9 45C79 51.3 64 70.5 64 92.5L64 448l-32 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0 192 0 32 0 0-32 0-448zM256 256c0 17.7-10.7 32-24 32s-24-14.3-24-32s10.7-32 24-32s24 14.3 24 32zm96-128l96 0 0 352c0 17.7 14.3 32 32 32l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0 0-320c0-35.3-28.7-64-64-64l-96 0 0 64z" /></svg>
            <span>Salir</span>
        </a>
    </div>
</aside>

{{-- Es recomendable tener bootstrap.bundle.min.js para que los dropdowns funcionen --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}

{{-- =================================================================
     SCRIPT UNIFICADO PARA ACTIVAR EL MÓDULO Y GESTIONAR CONEXIÓN
     ================================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- LÓGICA PARA ACTIVAR EL MENÚ DINÁMICAMENTE ---
    const currentUrl = window.location.href;
    const sidebarLinks = document.querySelectorAll('#sidebar .sidebar-nav .sidebar-link');

    sidebarLinks.forEach(link => {
        // Comparamos si la URL del enlace coincide con la URL actual de la página.
        // Usar `link.href` es clave porque los helpers de Laravel (url()) generan URLs absolutas.
        if (link.href === currentUrl) {
            
            // 1. Activa el elemento `li` padre del enlace actual
            // Usamos `.closest` para encontrar el ancestro más cercano que sea un item.
            const listItem = link.closest('.sidebar-item, .sidebar-item1, .sidebar-item2');
            if (listItem) {
                listItem.classList.add('active');
            }

            // 2. Si es un sub-menú, expande y activa el menú principal
            const dropdown = link.closest('.sidebar-dropdown');
            if (dropdown) {
                // Muestra el submenú
                dropdown.classList.add('show'); 
                
                // Busca el `<li>` padre del dropdown
                const parentItem = dropdown.closest('.sidebar-item');
                if (parentItem) {
                    // Marca también como activo el item principal del dropdown
                    parentItem.classList.add('active');
                    
                    // Busca el enlace que controla el dropdown y actualiza sus atributos
                    const toggleLink = parentItem.querySelector('.has-dropdown');
                    if (toggleLink) {
                        toggleLink.setAttribute('aria-expanded', 'true');
                        toggleLink.classList.remove('collapsed');
                    }
                }
            }
        }
    });

    // --- LÓGICA MEJORADA PARA ESTADO DE CONEXIÓN ---
    const offlineElements = document.querySelectorAll('.offline-hide');
    const statusIndicator = document.getElementById('connection-status');

    async function updateConnectionStatus() {
        try {
            // Verificar conexión real
            const online = navigator.onLine && await fetch('/csrf-token', { 
                method: 'HEAD',
                cache: 'no-cache',
                timeout: 3000 
            }).then(r => r.ok).catch(() => false);

            if (online) {
                // Conectado
                if (statusIndicator) {
                    statusIndicator.classList.remove('offline');
                    statusIndicator.classList.add('online');
                    statusIndicator.title = 'Conectado a Internet';
                }
                offlineElements.forEach(el => el.style.display = 'block');
            } else {
                // Desconectado
                if (statusIndicator) {
                    statusIndicator.classList.remove('online');
                    statusIndicator.classList.add('offline');
                    statusIndicator.title = 'Sin conexión a Internet';
                }
                offlineElements.forEach(el => el.style.display = 'none');
            }
        } catch (error) {
            console.log('Error verificando conectividad:', error);
            // En caso de error, asumir offline
            if (statusIndicator) {
                statusIndicator.classList.remove('online');
                statusIndicator.classList.add('offline');
                statusIndicator.title = 'Sin conexión a Internet';
            }
        }
    }

    // Verificar estado al cargar y cuando cambie
    updateConnectionStatus();
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
});
</script>

</body>
</html>