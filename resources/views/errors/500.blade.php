@extends('errors.layout')

@section('title', '500 — Engine Calculation Error')
@section('code', '500')
@section('badge_text', 'INTERNAL ENGINE ERROR')
@section('badge_color', 'var(--xp-error)')
@section('headline', 'Engine Calculation Error')
@section('message', 'An unexpected calculation or runtime exception occurred in the prediction pipeline. The local engine log has captured the trace.')

@section('meta')
    <span class="key">STATUS:</span>
    <span class="val">500 Server Error</span>
@endsection

