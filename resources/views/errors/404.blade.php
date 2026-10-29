@extends('errors.layout')

@section('title', '404 — Fixture Not Found')
@section('code', '404')
@section('badge_text', 'FIXTURE NOT FOUND')
@section('badge_color', 'var(--xp-orange)')
@section('headline', 'Requested Resource Not Found')
@section('message', 'The page, fixture endpoint, or match record you requested does not exist in the local database or may have been archived.')

@section('meta')
    <span class="key">URI:</span>
    <span class="val">{{ request()->path() }}</span>
@endsection
