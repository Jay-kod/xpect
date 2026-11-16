{{-- Dashboard High-Fidelity Skeletal Preloader Wireframe --}}
<div class="xp-dashboard-skeleton" style="display: flex; flex-direction: column; gap: 14px;">
    <!-- 1. Database Trust & Provenance Strip Skeleton -->
    <div class="db-provenance-strip" style="opacity: 0.95;">
        <div class="db-provenance-main" style="gap: 10px; flex-wrap: wrap;">
            <div class="xp-skeleton" style="width: 8px; height: 8px; border-radius: 50%;"></div>
            <div class="xp-skeleton" style="width: 170px; height: 14px; border-radius: 3px;"></div>
            <div class="xp-skeleton" style="width: 80px; height: 16px; border-radius: 4px;"></div>
            <div class="xp-skeleton" style="width: 90px; height: 16px; border-radius: 4px;"></div>
        </div>
        <div class="db-provenance-stats" style="gap: 8px; flex-wrap: wrap;">
            <div class="xp-skeleton" style="width: 130px; height: 12px; border-radius: 3px;"></div>
            <span class="db-bullet">&bull;</span>
            <div class="xp-skeleton" style="width: 90px; height: 12px; border-radius: 3px;"></div>
            <span class="db-bullet">&bull;</span>
            <div class="xp-skeleton" style="width: 140px; height: 12px; border-radius: 3px;"></div>
            <span class="db-bullet">&bull;</span>
            <div class="xp-skeleton" style="width: 75px; height: 16px; border-radius: 10px;"></div>
        </div>
    </div>

    <!-- 2. SportyBet Horizontal League Filter Bar Skeleton -->
    <div class="sporty-leagues-bar" style="gap: 8px; margin-bottom: 2px;">
        @foreach (['110px', '95px', '105px', '100px', '95px', '115px'] as $width)
            <div class="sporty-league-chip" style="width: {{ $width }}; height: 32px; border-radius: 20px; display: flex; align-items: center; justify-content: space-between; padding: 6px 10px;">
                <div class="xp-skeleton" style="width: 14px; height: 14px; border-radius: 50%;"></div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 45%; height: 10px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 24px; height: 12px; border-radius: 3px;"></div>
            </div>
        @endforeach
    </div>

    <!-- 3. Day Switcher & Matchdays Bar Skeleton -->
    <div class="sporty-day-switcher" style="padding: 8px 12px; margin-bottom: 4px;">
        <div class="day-switcher-left" style="gap: 6px;">
            <div class="day-pill active" style="width: 130px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; padding: 4px 8px;">
                <div class="xp-skeleton" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                <div class="xp-skeleton xp-skeleton-text" style="width: 55%; height: 10px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 18px; height: 14px; border-radius: 8px;"></div>
            </div>
            <div class="day-pill" style="width: 140px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; padding: 4px 8px;">
                <div class="xp-skeleton xp-skeleton-text" style="width: 70%; height: 10px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 18px; height: 14px; border-radius: 8px;"></div>
            </div>
            <div class="day-pill" style="width: 110px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; padding: 4px 8px;">
                <div class="xp-skeleton xp-skeleton-text" style="width: 60%; height: 10px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 18px; height: 14px; border-radius: 8px;"></div>
            </div>
            <div class="day-pill" style="width: 100px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; padding: 4px 8px;">
                <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 10px; margin: 0;"></div>
                <div class="xp-skeleton" style="width: 22px; height: 14px; border-radius: 8px;"></div>
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <div class="xp-skeleton" style="width: 140px; height: 26px; border-radius: 4px;"></div>
            <div class="xp-skeleton" style="width: 110px; height: 26px; border-radius: 4px;"></div>
        </div>
    </div>

    <!-- 4. Main Matches Grid & Sidebar Layout Skeleton -->
    <div class="matches-grid-container" style="margin-top: 4px;">
        <!-- Left: Match Cards Feed Skeleton -->
        <div style="min-width: 0; display: flex; flex-direction: column; gap: 16px;">
            <!-- Marquee Spotlight Skeleton Card -->
            <div class="card" style="border: 1.5px solid rgba(217, 78, 0, 0.4); background: #0A0A0E; padding: 18px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <div class="xp-skeleton" style="width: 85px; height: 18px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="width: 120px; height: 14px; border-radius: 3px;"></div>
                    </div>
                    <div class="xp-skeleton" style="width: 90px; height: 18px; border-radius: 4px;"></div>
                </div>

                <!-- Teams Showcase -->
                <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px; align-items: center; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                        <div style="width: 70%; text-align: right;">
                            <div class="xp-skeleton xp-skeleton-text" style="width: 85%; height: 16px; margin-left: auto; margin-bottom: 5px;"></div>
                            <div class="xp-skeleton xp-skeleton-text" style="width: 55%; height: 11px; margin-left: auto; margin-bottom: 0;"></div>
                        </div>
                        <div class="xp-skeleton xp-skeleton-badge" style="width: 36px; height: 36px; flex-shrink: 0;"></div>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                        <div class="xp-skeleton" style="width: 32px; height: 18px; border-radius: 10px;"></div>
                        <div class="xp-skeleton" style="width: 60px; height: 10px; border-radius: 3px;"></div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 12px;">
                        <div class="xp-skeleton xp-skeleton-badge" style="width: 36px; height: 36px; flex-shrink: 0;"></div>
                        <div style="width: 70%;">
                            <div class="xp-skeleton xp-skeleton-text" style="width: 85%; height: 16px; margin-bottom: 5px;"></div>
                            <div class="xp-skeleton xp-skeleton-text" style="width: 55%; height: 11px; margin-bottom: 0;"></div>
                        </div>
                    </div>
                </div>

                <!-- Odds Grid Skeleton (1, X, 2) -->
                <div class="sporty-odds-grid" style="gap: 12px; margin-bottom: 14px;">
                    <div class="sporty-odd-card" style="height: 72px; padding: 10px;">
                        <div class="xp-skeleton" style="width: 50%; height: 10px; margin-bottom: 6px;"></div>
                        <div class="xp-skeleton" style="width: 70%; height: 20px; margin-bottom: 4px;"></div>
                        <div class="xp-skeleton" style="width: 40%; height: 10px;"></div>
                    </div>
                    <div class="sporty-odd-card" style="height: 72px; padding: 10px;">
                        <div class="xp-skeleton" style="width: 50%; height: 10px; margin-bottom: 6px;"></div>
                        <div class="xp-skeleton" style="width: 70%; height: 20px; margin-bottom: 4px;"></div>
                        <div class="xp-skeleton" style="width: 40%; height: 10px;"></div>
                    </div>
                    <div class="sporty-odd-card favored" style="height: 72px; padding: 10px; border-color: var(--xp-orange);">
                        <div class="xp-skeleton" style="width: 50%; height: 10px; margin-bottom: 6px;"></div>
                        <div class="xp-skeleton" style="width: 70%; height: 20px; margin-bottom: 4px;"></div>
                        <div class="xp-skeleton" style="width: 40%; height: 10px;"></div>
                    </div>
                </div>

                <!-- 3 Analytical Systems Box Skeleton -->
                <div style="background: #060609; border: 1px solid var(--xp-border); border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <div class="xp-skeleton" style="width: 140px; height: 12px;"></div>
                        <div class="xp-skeleton" style="width: 100px; height: 12px;"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div class="xp-skeleton" style="height: 48px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="height: 48px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="height: 48px; border-radius: 4px;"></div>
                    </div>
                </div>

                <!-- Bottom Markets Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--xp-border); padding-top: 10px;">
                    <div style="display: flex; gap: 8px;">
                        <div class="xp-skeleton" style="width: 65px; height: 18px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="width: 65px; height: 18px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="width: 80px; height: 18px; border-radius: 4px;"></div>
                    </div>
                    <div class="xp-skeleton" style="width: 110px; height: 22px; border-radius: 4px;"></div>
                </div>
            </div>

            <!-- Standard Match Cards Skeletons (2 Items) -->
            @for ($m = 0; $m < 2; $m++)
                <div class="card" style="background: #09090C; border: 1px solid var(--xp-border); padding: 16px;">
                    <!-- Top Sub-Bar -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <div class="xp-skeleton" style="width: 60px; height: 16px; border-radius: 3px;"></div>
                            <div class="xp-skeleton" style="width: 130px; height: 12px; border-radius: 3px;"></div>
                        </div>
                        <div class="xp-skeleton" style="width: 75px; height: 16px; border-radius: 4px;"></div>
                    </div>

                    <!-- Teams Row -->
                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 14px; align-items: center; margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                            <div style="width: 75%; text-align: right;">
                                <div class="xp-skeleton xp-skeleton-text" style="width: 80%; height: 15px; margin-left: auto; margin-bottom: 4px;"></div>
                                <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 10px; margin-left: auto; margin-bottom: 0;"></div>
                            </div>
                            <div class="xp-skeleton xp-skeleton-badge" style="width: 30px; height: 30px; flex-shrink: 0;"></div>
                        </div>
                        <div class="xp-skeleton" style="width: 28px; height: 18px; border-radius: 12px;"></div>
                        <div style="display: flex; align-items: center; justify-content: flex-start; gap: 10px;">
                            <div class="xp-skeleton xp-skeleton-badge" style="width: 30px; height: 30px; flex-shrink: 0;"></div>
                            <div style="width: 75%;">
                                <div class="xp-skeleton xp-skeleton-text" style="width: 80%; height: 15px; margin-bottom: 4px;"></div>
                                <div class="xp-skeleton xp-skeleton-text" style="width: 50%; height: 10px; margin-bottom: 0;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Odds Grid (1, X, 2) -->
                    <div class="sporty-odds-grid" style="gap: 10px; margin-bottom: 12px;">
                        <div class="sporty-odd-card favored" style="height: 64px; padding: 8px;">
                            <div class="xp-skeleton" style="width: 40%; height: 9px; margin-bottom: 4px;"></div>
                            <div class="xp-skeleton" style="width: 60%; height: 18px; margin-bottom: 3px;"></div>
                            <div class="xp-skeleton" style="width: 35%; height: 9px;"></div>
                        </div>
                        <div class="sporty-odd-card" style="height: 64px; padding: 8px;">
                            <div class="xp-skeleton" style="width: 40%; height: 9px; margin-bottom: 4px;"></div>
                            <div class="xp-skeleton" style="width: 60%; height: 18px; margin-bottom: 3px;"></div>
                            <div class="xp-skeleton" style="width: 35%; height: 9px;"></div>
                        </div>
                        <div class="sporty-odd-card" style="height: 64px; padding: 8px;">
                            <div class="xp-skeleton" style="width: 40%; height: 9px; margin-bottom: 4px;"></div>
                            <div class="xp-skeleton" style="width: 60%; height: 18px; margin-bottom: 3px;"></div>
                            <div class="xp-skeleton" style="width: 35%; height: 9px;"></div>
                        </div>
                    </div>

                    <!-- Mini Systems Strip -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                        <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                        <div class="xp-skeleton" style="height: 38px; border-radius: 4px;"></div>
                    </div>

                    <!-- Markets Footer -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--xp-border); padding-top: 8px;">
                        <div style="display: flex; gap: 6px;">
                            <div class="xp-skeleton" style="width: 55px; height: 16px; border-radius: 3px;"></div>
                            <div class="xp-skeleton" style="width: 55px; height: 16px; border-radius: 3px;"></div>
                        </div>
                        <div class="xp-skeleton" style="width: 85px; height: 16px; border-radius: 3px;"></div>
                    </div>
                </div>
            @endfor
        </div>

        <!-- Right: View Navigator Sidebar Skeleton -->
        <div class="matches-sidebar-col">
            <div class="view-sidebar-card" style="gap: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--xp-border); padding-bottom: 8px; margin-bottom: 4px;">
                    <div class="xp-skeleton" style="width: 100px; height: 12px; border-radius: 2px;"></div>
                    <div class="xp-skeleton" style="width: 16px; height: 16px; border-radius: 50%;"></div>
                </div>

                @for ($s = 0; $s < 6; $s++)
                    <div class="view-nav-btn" style="height: 38px; padding: 6px 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px; width: 75%;">
                            <div class="xp-skeleton" style="width: 14px; height: 14px; border-radius: 3px; flex-shrink: 0;"></div>
                            <div class="xp-skeleton xp-skeleton-text" style="width: 65%; height: 11px; margin: 0;"></div>
                        </div>
                        <div class="xp-skeleton" style="width: 22px; height: 14px; border-radius: 8px;"></div>
                    </div>
                @endfor

                <!-- Mini Model Performance Skeleton Card -->
                <div style="background: #08080C; border: 1px solid var(--xp-border); border-radius: 6px; padding: 10px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <div class="xp-skeleton" style="width: 80px; height: 10px;"></div>
                        <div class="xp-skeleton" style="width: 35px; height: 10px;"></div>
                    </div>
                    <div class="xp-skeleton" style="width: 100%; height: 4px; border-radius: 2px; margin-bottom: 6px;"></div>
                    <div class="xp-skeleton" style="width: 60%; height: 9px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
