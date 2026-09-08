@extends('layouts.app')

@section('title', 'Mis Jugadas')
@section('meta_description', 'Panel de jugadas de Agencia N°5801')

@section('content')
<div class="container" style="padding-top: 2.5rem; padding-bottom: 3rem;">

    <div class="placeholder-panel">
        <span class="placeholder-icon" aria-hidden="true">🎯</span>
        <h1 class="placeholder-title">Mis Jugadas</h1>
        <p class="placeholder-desc">
            Bienvenido/a, <strong>{{ auth()->user()->name }}</strong>.<br>
            Desde aquí podrás registrar y hacer seguimiento de tus jugadas simuladas.
        </p>

    </div>

</div>
@endsection
