@extends('layouts.app')
@section('title', 'Acceso Denegado')
@section('content')
<div class="container-fluid">
    <div class="text-center" style="margin-top: 100px;">
        <div class="error mx-auto" data-text="401">
            <p class="m-0" style="font-size: 7rem; color: #00476D;">401</p>
        </div>
        <p class="text-dark mb-5 lead" style="font-size: 1.5rem;">Acceso No Autorizado</p>
        <p class="text-gray-500 mb-4">No tienes permisos suficientes para acceder a esta página.</p>
        <a href="{{ url('/') }}" class="btn btn-primary" style="background-color: #00476D !important;">
            <i class="fas fa-arrow-left me-2"></i>Volver al Inicio
        </a>
    </div>
</div>
@endsection
