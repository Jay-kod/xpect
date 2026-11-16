{{-- Data Health & Integrity Auditor Skeletal Preloader Wireframe --}}
<div class="xp-system-health-skeleton" style="display: flex; flex-direction: column; gap: 20px;">
    <!-- Page Header Skeleton -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 2px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <div class="xp-skeleton xp-skeleton-title" style="width: 320px; height: 26px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 120px; height: 20px; border-radius: 4px; background: rgba(61,220,132,0.3);"></div>
            </div>
            <div class="xp-skeleton xp-skeleton-text" style="width: 480px; height: 13px; margin: 0;"></div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div class="xp-skeleton" style="width: 130px; height: 32px; border-radius: 6px;"></div>
            <div class="xp-skeleton" style="width: 160px; height: 32px; border-radius: 6px; background: rgba(217,78,0,0.35);"></div>
        </div>
    </div>

    <!-- 4 Vital Audit KPI Cards Skeleton -->
    <div class="grid-kpi-4" style="margin-bottom: 6px;">
        @for ($k = 0; $k < 4; $k++)
            <div class="kpi-card" style="min-height: 100px;">
                <div class="xp-skeleton xp-skeleton-text" style="width: 55%; height: 12px; margin-bottom: 8px;"></div>
                <div class="xp-skeleton" style="width: 70%; height: 26px; border-radius: 4px; margin-bottom: 6px;"></div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 45%; height: 10px; margin: 0;"></div>
            </div>
        @endfor
    </div>

    <!-- Integrity Checklist & Verification Cards Skeleton -->
    <div class="card" style="padding: 18px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--xp-border); padding-bottom: 10px;">
            <div class="xp-skeleton xp-skeleton-title" style="width: 220px; height: 18px; margin: 0;"></div>
            <div class="xp-skeleton" style="width: 90px; height: 16px; border-radius: 4px;"></div>
        </div>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @for ($c = 0; $c < 4; $c++)
                <div style="display: flex; justify-content: space-between; align-items: center; background: #08080C; border: 1px solid var(--xp-border-subtle); border-radius: 6px; padding: 12px 14px;">
                    <div style="display: flex; align-items: center; gap: 10px; width: 70%;">
                        <div class="xp-skeleton xp-skeleton-badge" style="width: 18px; height: 18px; flex-shrink: 0; background: rgba(61,220,132,0.35);"></div>
                        <div class="xp-skeleton xp-skeleton-text" style="width: 80%; height: 13px; margin: 0;"></div>
                    </div>
                    <div class="xp-skeleton" style="width: 80px; height: 20px; border-radius: 4px;"></div>
                </div>
            @endfor
        </div>
    </div>
</div>
