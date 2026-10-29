@extends('errors.layout')

@section('title', '503 — Engine Under Maintenance')
@section('code', '503')
@section('badge_text', 'MAINTENANCE MODE')
@section('badge_color', 'var(--xp-orange)')
@section('headline', 'Engine Under Maintenance')
@section('message', 'The Xpect prediction engine is currently completing a database migration, backtest computation, or cache replay. Check back in a moment.')

@section('meta')
    <span class="key">ENGINE STATUS:</span>
    <span class="val">Maintenance Active</span>
@endsection

