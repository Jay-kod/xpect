@extends('errors.layout')

@section('title', '429 — Rate Limit / Quota Guard')
@section('code', '429')
@section('badge_text', 'QUOTA GUARD ACTIVE')
@section('badge_color', 'var(--xp-orange)')
@section('headline', 'API Quota Guard Active')
@section('message', 'Too many requests have been dispatched to external football providers. The local quota guard paused additional calls to protect API limits.')

@section('meta')
    <span class="key">QUOTA GUARD:</span>
    <span class="val">Throttled per football.php configuration</span>
@endsection

