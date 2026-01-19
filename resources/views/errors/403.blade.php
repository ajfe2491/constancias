@extends('errors.layout')

@section('title', __('Acceso Denegado'))
@section('code', '403')
@section('message', __('Acceso Restringido'))
@section('description', __('Lo sentimos, no tienes los permisos necesarios para acceder a esta página. Si crees que esto es un error, contacta al administrador.'))

@section('image')
    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-pink-500 opacity-80" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
@endsection