@extends('errors.layout')

@section('title', __('Página No Encontrada'))
@section('code', '404')
@section('message', __('¡Ups! Página no encontrada'))
@section('description', __('Lo sentimos, no pudimos encontrar la página que buscas. Pudo haber sido eliminada o la dirección es incorrecta.'))

@section('image')
    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-indigo-500 opacity-80" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection