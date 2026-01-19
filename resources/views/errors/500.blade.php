@extends('errors.layout')

@section('title', __('Error del Servidor'))
@section('code', '500')
@section('message', __('Error Interno del Servidor'))
@section('description', __('Algo salió mal de nuestro lado. Estamos trabajando para solucionarlo. Por favor, intenta de nuevo más tarde.'))

@section('image')
    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-red-500 opacity-80" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
@endsection