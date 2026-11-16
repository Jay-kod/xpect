@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="xp-pagination-container">
        <!-- Summary Stats (Showing X to Y of Z results) -->
        <div class="xp-pagination-summary">
            <span class="xp-pagination-summary-text">
                Showing
                <strong class="xp-pagination-number">{{ number_format($paginator->firstItem() ?? 0) }}</strong>
                to
                <strong class="xp-pagination-number">{{ number_format($paginator->lastItem() ?? 0) }}</strong>
                of
                <strong class="xp-pagination-total">{{ number_format($paginator->total()) }}</strong>
                fixtures
            </span>
            <span class="xp-pagination-page-pill">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>
        </div>

        <!-- Controls: Previous Button, Page Numbers, Next Button -->
        <div class="xp-pagination-controls">
            <!-- PREVIOUS BUTTON -->
            @if ($paginator->onFirstPage())
                <span class="xp-page-btn xp-page-prev disabled" aria-disabled="true" aria-label="Previous page">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="xp-prev-icon">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Previous</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="xp-page-btn xp-page-prev active-btn" aria-label="Previous page">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="xp-prev-icon">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Previous</span>
                </a>
            @endif

            <!-- PAGE NUMBERS (Shown on desktop/tablet) -->
            <div class="xp-page-numbers-group">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="xp-page-btn xp-page-dots" aria-disabled="true">
                            <span>{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="xp-page-btn xp-page-num current" aria-current="page">
                                    <span>{{ $page }}</span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="xp-page-btn xp-page-num" aria-label="Go to page {{ $page }}">
                                    <span>{{ $page }}</span>
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <!-- NEXT BUTTON -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="xp-page-btn xp-page-next active-btn" aria-label="Next page">
                    <span>Next</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="xp-next-icon">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @else
                <span class="xp-page-btn xp-page-next disabled" aria-disabled="true" aria-label="Next page">
                    <span>Next</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="xp-next-icon">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
