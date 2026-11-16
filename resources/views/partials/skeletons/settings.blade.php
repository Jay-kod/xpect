{{-- Settings & AI Providers Skeletal Preloader Wireframe --}}
<div class="xp-settings-skeleton" style="display: flex; flex-direction: column; gap: 20px;">
    <!-- Settings Header Skeleton -->
    <div class="settings-header">
        <div>
            <div class="xp-skeleton xp-skeleton-title" style="width: 260px; height: 26px; margin-bottom: 6px;"></div>
            <div class="xp-skeleton xp-skeleton-text" style="width: 420px; height: 13px; margin: 0;"></div>
        </div>
        <div class="xp-skeleton" style="width: 130px; height: 32px; border-radius: 6px;"></div>
    </div>

    <!-- 2-Column Grid (Keys Configuration & System Cards) -->
    <div class="settings-grid">
        <!-- Left: API Configuration Cards Skeleton -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- LLM Providers Selection Card Skeleton -->
            <div class="settings-card">
                <div class="card-heading-row">
                    <div class="xp-skeleton xp-skeleton-title" style="width: 170px; height: 18px; margin: 0;"></div>
                    <div class="xp-skeleton" style="width: 70px; height: 16px; border-radius: 3px;"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="xp-skeleton" style="height: 64px; border-radius: 6px;"></div>
                    <div class="xp-skeleton" style="height: 64px; border-radius: 6px; border: 1px solid var(--xp-orange);"></div>
                    <div class="xp-skeleton" style="height: 64px; border-radius: 6px;"></div>
                </div>
            </div>

            <!-- API Keys Form Skeleton -->
            <div class="settings-card">
                <div class="card-heading-row">
                    <div class="xp-skeleton xp-skeleton-title" style="width: 140px; height: 18px; margin: 0;"></div>
                    <div class="xp-skeleton" style="width: 80px; height: 14px; border-radius: 3px;"></div>
                </div>
                @for ($k = 0; $k < 3; $k++)
                    <div class="form-row">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <div class="xp-skeleton" style="width: 110px; height: 12px;"></div>
                            <div class="xp-skeleton" style="width: 50px; height: 12px;"></div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div class="xp-skeleton" style="flex: 1; height: 36px; border-radius: 6px;"></div>
                            <div class="xp-skeleton" style="width: 70px; height: 36px; border-radius: 6px;"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Right: Quota & System Maintenance Skeletons -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="settings-card">
                <div class="card-heading-row">
                    <div class="xp-skeleton xp-skeleton-title" style="width: 150px; height: 18px; margin: 0;"></div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div class="xp-skeleton" style="height: 48px; border-radius: 6px;"></div>
                    <div class="xp-skeleton" style="height: 48px; border-radius: 6px;"></div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-heading-row">
                    <div class="xp-skeleton xp-skeleton-title" style="width: 160px; height: 18px; margin: 0;"></div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                    <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
