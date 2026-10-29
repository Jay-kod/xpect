@extends('errors.layout')

@section('title', '419 — Session Expired')
@section('code', '419')
@section('badge_text', 'TOKEN TIMEOUT')
@section('badge_color', 'var(--xp-away)')
@section('headline', 'Security Token Expired')
@section('message', 'Your CSRF session security token has expired. Please refresh the page or return to the dashboard to initiate a fresh session.')

@section('meta')
    <span class="key">SESSION LIFETIME:</span>
    <span class="val">{{ config('session.lifetime') }} minutes</span>
@endsection

