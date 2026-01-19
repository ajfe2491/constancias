@extends('errors.layout')

@section('title', __('Página Expirada'))
@section('code', '419')
@section('message', __('La Pagina ha Expirado'))
@section('description', __('Tu sesión ha caducado por inactividad. Por favor, recarga la página e intenta nuevamente.'))

@section('image')
    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-orange-400 opacity-80" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection