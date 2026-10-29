@props([
    'team' => null,
    'crest' => null,
    'name' => null,
    'size' => 20,
    'class' => '',
])

@php
    $crestUrl = $crest ?? ($team?->crest_url);
    $displayName = $name ?? ($team?->short_name ?? ($team?->name ?? 'Team'));
    $initials = $team?->initials;

    if (empty($initials)) {
        $clean = preg_replace('/\b(FC|AFC|CF|SC|SSC|SV|VfB|VfL|AS)\b/i', '', (string) $displayName);
        $words = array_values(array_filter(explode(' ', trim((string) $clean))));
        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $initials = strtoupper(substr(trim((string) $clean), 0, min(3, strlen(trim((string) $clean)))) ?: 'FC');
        }
    }

    $fallbackFontSize = max(8, (int) round($size * 0.42));
    $fallbackRadius = (int) round($size * 0.25);
@endphp

<span class="team-badge-wrapper inline-flex items-center justify-center {{ $class }}" style="display: inline-flex; vertical-align: middle; flex-shrink: 0; width: {{ $size }}px; height: {{ $size }}px;">
    @if(!empty($crestUrl))
        <img 
            src="{{ $crestUrl }}" 
            alt="{{ $displayName }}" 
            width="{{ $size }}" 
            height="{{ $size }}" 
            loading="lazy" 
            decoding="async" 
            class="team-crest-img"
            style="width: {{ $size }}px; height: {{ $size }}px; object-fit: contain; flex-shrink: 0; display: block;" 
            onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';"
        />
    @endif
    <span 
        class="team-badge-fallback" 
        style="display: {{ empty($crestUrl) ? 'inline-flex' : 'none' }}; width: {{ $size }}px; height: {{ $size }}px; font-size: {{ $fallbackFontSize }}px; font-weight: 700; border-radius: {{ $fallbackRadius }}px; background: #141418; border: 1px solid var(--xp-border, #1E1E22); color: var(--xp-orange, #D94E00); align-items: center; justify-content: center; flex-shrink: 0; line-height: 1; letter-spacing: -0.02em; user-select: none;" 
        title="{{ $displayName }}"
    >
        {{ $initials }}
    </span>
</span>
