{{-- Today's Fixtures Hub Skeletal Preloader Wireframe --}}
<div class="xp-today-skeleton" style="display: flex; flex-direction: column; gap: 18px;">
    <!-- Header & Date Controls Bar Skeleton -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 6px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <div class="xp-skeleton xp-skeleton-title" style="width: 240px; height: 26px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 90px; height: 20px; border-radius: 4px;"></div>
            </div>
            <div class="xp-skeleton xp-skeleton-text" style="width: 380px; height: 12px; margin: 0;"></div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Date Switcher Skeleton -->
            <div style="display: flex; align-items: center; background: #0C0C0E; border: 1px solid var(--xp-border); border-radius: 6px; padding: 4px; gap: 6px;">
                <div class="xp-skeleton" style="width: 50px; height: 22px; border-radius: 3px;"></div>
                <div class="xp-skeleton" style="width: 65px; height: 22px; border-radius: 3px;"></div>
                <div class="xp-skeleton" style="width: 55px; height: 22px; border-radius: 3px; background: rgba(217, 78, 0, 0.4);"></div>
                <div class="xp-skeleton" style="width: 65px; height: 22px; border-radius: 3px;"></div>
                <div class="xp-skeleton" style="width: 50px; height: 22px; border-radius: 3px;"></div>
            </div>
            <!-- Calendar Picker Skeleton -->
            <div class="xp-skeleton" style="width: 130px; height: 32px; border-radius: 6px;"></div>
            <!-- Predict All Button Skeleton -->
            <div class="xp-skeleton" style="width: 140px; height: 32px; border-radius: 6px; background: rgba(217, 78, 0, 0.35);"></div>
        </div>
    </div>

    <!-- League Fixtures Groups Skeleton -->
    @for ($g = 0; $g < 2; $g++)
        <div class="card" style="padding: 16px; background: var(--xp-surface);">
            <!-- League Section Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--xp-border); padding-bottom: 12px; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="xp-skeleton xp-skeleton-badge" style="width: 24px; height: 24px;"></div>
                    <div class="xp-skeleton xp-skeleton-title" style="width: 180px; height: 18px; margin: 0;"></div>
                    <div class="xp-skeleton" style="width: 45px; height: 16px; border-radius: 4px;"></div>
                </div>
                <div class="xp-skeleton" style="width: 80px; height: 18px; border-radius: 4px;"></div>
            </div>

            <!-- Matches in League (2 items) -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @for ($m = 0; $m < 2; $m++)
                    <div style="background: #08080B; border: 1px solid var(--xp-border-subtle); border-radius: 6px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <div class="xp-skeleton" style="width: 100px; height: 12px;"></div>
                            <div class="xp-skeleton" style="width: 60px; height: 12px;"></div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px; align-items: center; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                <div class="xp-skeleton xp-skeleton-text" style="width: 60%; height: 14px; margin: 0;"></div>
                                <div class="xp-skeleton xp-skeleton-badge" style="width: 24px; height: 24px;"></div>
                            </div>
                            <div class="xp-skeleton" style="width: 24px; height: 16px; border-radius: 8px;"></div>
                            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 8px;">
                                <div class="xp-skeleton xp-skeleton-badge" style="width: 24px; height: 24px;"></div>
                                <div class="xp-skeleton xp-skeleton-text" style="width: 60%; height: 14px; margin: 0;"></div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                            <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                            <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                            <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @endfor
</div>
