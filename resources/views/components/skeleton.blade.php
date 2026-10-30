@props([
    'type' => 'card', // card, kpi, match, row, text, badge, table
    'count' => 1,
    'width' => null,
    'height' => null,
    'class' => '',
])

@for ($i = 0; $i < $count; $i++)
    @if ($type === 'kpi')
        <div class="xp-skeleton-card {{ $class }}" style="min-height: 105px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 45%; height: 11px; margin-bottom: 8px;"></div>
                <div class="xp-skeleton" style="width: 65%; height: 28px; margin-bottom: 8px; border-radius: 4px;"></div>
            </div>
            <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 10px; margin-bottom: 0;"></div>
        </div>
    @elseif ($type === 'match')
        <div class="xp-skeleton-card {{ $class }}" style="margin-bottom: 12px; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div class="xp-skeleton xp-skeleton-text" style="width: 25%; height: 10px; margin: 0;"></div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 15%; height: 10px; margin: 0;"></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 14px; align-items: center; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <div style="width: 60%; text-align: right;">
                        <div class="xp-skeleton xp-skeleton-text" style="width: 80%; height: 14px; margin-left: auto; margin-bottom: 4px;"></div>
                        <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 10px; margin-left: auto; margin-bottom: 0;"></div>
                    </div>
                    <div class="xp-skeleton xp-skeleton-badge" style="width: 24px; height: 24px;"></div>
                </div>
                <div class="xp-skeleton" style="width: 20px; height: 12px; border-radius: 3px;"></div>
                <div style="display: flex; align-items: center; justify-content: flex-start; gap: 10px;">
                    <div class="xp-skeleton xp-skeleton-badge" style="width: 24px; height: 24px;"></div>
                    <div style="width: 60%;">
                        <div class="xp-skeleton xp-skeleton-text" style="width: 80%; height: 14px; margin-bottom: 4px;"></div>
                        <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 10px; margin-bottom: 0;"></div>
                    </div>
                </div>
            </div>
            <div class="xp-skeleton" style="width: 100%; height: 24px; border-radius: 4px;"></div>
        </div>
    @elseif ($type === 'row')
        <div style="display: flex; align-items: center; gap: 12px; padding: 10px 0; {{ $class }}">
            <div class="xp-skeleton xp-skeleton-badge" style="width: 20px; height: 20px; flex-shrink: 0;"></div>
            <div class="xp-skeleton xp-skeleton-text" style="flex: 1; height: 13px; margin: 0;"></div>
            <div class="xp-skeleton xp-skeleton-text" style="width: 60px; height: 13px; margin: 0;"></div>
        </div>
    @elseif ($type === 'table')
        <div class="xp-skeleton-card {{ $class }}" style="padding: 0; overflow: hidden;">
            <div style="padding: 12px 16px; background: #101014; border-bottom: 1px solid var(--xp-border); display: flex; justify-content: space-between;">
                <div class="xp-skeleton xp-skeleton-text" style="width: 30%; height: 12px; margin: 0;"></div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 15%; height: 12px; margin: 0;"></div>
            </div>
            <div style="padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;">
                @for ($j = 0; $j < 5; $j++)
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                        <div class="xp-skeleton xp-skeleton-text" style="width: 40%; height: 12px; margin: 0;"></div>
                        <div class="xp-skeleton xp-skeleton-text" style="width: 20%; height: 12px; margin: 0;"></div>
                        <div class="xp-skeleton xp-skeleton-text" style="width: 15%; height: 12px; margin: 0;"></div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="xp-skeleton {{ $class }}" style="width: {{ $width ?? '100%' }}; height: {{ $height ?? '20px' }};"></div>
    @endif
@endfor
