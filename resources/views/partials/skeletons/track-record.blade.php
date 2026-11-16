{{-- Track Record & Analytics Skeletal Preloader Wireframe --}}
<div class="xp-track-record-skeleton" style="display: flex; flex-direction: column; gap: 20px;">
    <!-- Page Header Skeleton -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 4px;">
        <div>
            <div class="xp-skeleton xp-skeleton-title" style="width: 280px; height: 26px; margin-bottom: 6px;"></div>
            <div class="xp-skeleton xp-skeleton-text" style="width: 440px; height: 13px; margin: 0;"></div>
        </div>
        <div class="xp-skeleton" style="width: 150px; height: 20px; border-radius: 4px;"></div>
    </div>

    <!-- 4 Top Metric KPI Cards Skeleton -->
    <div class="grid-4" style="margin-bottom: 8px;">
        @for ($k = 0; $k < 4; $k++)
            <div class="kpi-card" style="min-height: 110px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 12px; margin-bottom: 8px;"></div>
                    <div class="xp-skeleton" style="width: 65%; height: 28px; border-radius: 4px; margin-bottom: 8px; {{ $k === 0 ? 'background:rgba(61,220,132,0.3);' : ($k === 1 ? 'background:rgba(217,78,0,0.3);' : '') }}"></div>
                </div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 60%; height: 10px; margin: 0;"></div>
            </div>
        @endfor
    </div>

    <!-- 2-Column Analytics Section (Calibration Chart & Bins Table) -->
    <div class="grid-2" style="gap: 18px;">
        <!-- Calibration Curve Container Skeleton -->
        <div class="card" style="padding: 18px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                <div class="xp-skeleton xp-skeleton-title" style="width: 170px; height: 16px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 80px; height: 14px; border-radius: 3px;"></div>
            </div>
            <div class="xp-skeleton" style="width: 100%; height: 240px; border-radius: 6px; margin-bottom: 12px;"></div>
            <div style="display: flex; justify-content: center; gap: 16px;">
                <div class="xp-skeleton" style="width: 90px; height: 12px;"></div>
                <div class="xp-skeleton" style="width: 90px; height: 12px;"></div>
            </div>
        </div>

        <!-- 10-Bin Calibration Table Skeleton -->
        <div class="card" style="padding: 18px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                <div class="xp-skeleton xp-skeleton-title" style="width: 190px; height: 16px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 70px; height: 14px; border-radius: 3px;"></div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @for ($b = 0; $b < 6; $b++)
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--xp-border-subtle); padding-bottom: 8px;">
                        <div class="xp-skeleton" style="width: 60px; height: 12px;"></div>
                        <div class="xp-skeleton" style="width: 45px; height: 12px;"></div>
                        <div class="xp-skeleton" style="width: 45px; height: 12px;"></div>
                        <div class="xp-skeleton" style="width: 50px; height: 14px; border-radius: 3px;"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
