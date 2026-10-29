@extends('errors.layout')

@section('title', '403 — Access Restricted')
@section('code', '403')
@section('badge_text', 'RESTRICTED BOUNDARY')
@section('badge_color', 'var(--xp-error)')
@section('headline', 'Access Restricted')
@section('message', 'This endpoint or action is restricted. Single-user local inspection rules or boundary security guards prevented access.')

@section('meta')
    <span class="key">ENVIRONMENT:</span>
    <span class="val">{{ app()->environment() }} (Local Only)</span>
@endsection
