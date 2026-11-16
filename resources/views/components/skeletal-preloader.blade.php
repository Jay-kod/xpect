@props([
    'page' => 'dashboard', // dashboard, today, track-record, settings, system-health
    'id' => null,
    'class' => '',
])

<div id="{{ $id ?? ($page . '-skeletal-preloader') }}"
     class="xp-skeletal-preloader {{ $class }}"
     style="{{ request()->has('skeleton') ? 'display: block;' : 'display: none;' }}"
     aria-hidden="true">
    <x-skeleton :type="$page" />
</div>
