@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; width: 100%;">
        {{-- Left: Results Count in Thai --}}
        <div style="font-size: 13.5px; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
            <span>แสดง</span>
            @if ($paginator->firstItem())
                <span style="font-weight: 600; color: var(--text-main);">{{ number_format($paginator->firstItem()) }}</span>
                <span>ถึง</span>
                <span style="font-weight: 600; color: var(--text-main);">{{ number_format($paginator->lastItem()) }}</span>
            @else
                <span style="font-weight: 600; color: var(--text-main);">{{ number_format($paginator->count()) }}</span>
            @endif
            <span>จากทั้งหมด</span>
            <span style="font-weight: 600; color: var(--text-main);">{{ number_format($paginator->total()) }}</span>
            <span>รายการ</span>
        </div>

        {{-- Right: In the exact same row/line with "ก่อนหน้า" and "ถัดไป" --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px;">
            {{-- Per Page Selector --}}
            <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: var(--text-muted);">
                <span>แสดงต่อหน้า:</span>
                <select class="form-select form-select-sm" style="width: auto; min-width: 100px; padding: 5px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; border: 1px solid var(--border); background-color: #ffffff; color: var(--text-main); cursor: pointer;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page', $paginator->perPage()) == 10 ? 'selected' : '' }}>10 รายการ</option>
                    <option value="20" {{ request('per_page', $paginator->perPage()) == 20 ? 'selected' : '' }}>20 รายการ</option>
                    <option value="50" {{ request('per_page', $paginator->perPage()) == 50 ? 'selected' : '' }}>50 รายการ</option>
                    <option value="100" {{ request('per_page', $paginator->perPage()) == 100 ? 'selected' : '' }}>100 รายการ</option>
                </select>
            </div>

            {{-- Pagination Buttons (« ก่อนหน้า, 1, 2, ..., ถัดไป ») --}}
            @if ($paginator->hasPages())
                <ul class="pagination" style="display: flex; gap: 4px; align-items: center; list-style: none; margin: 0; padding: 0;">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" style="padding: 6px 12px; font-size: 13px; border-radius: 6px; border: 1px solid var(--border); background: #f8fafc; color: #94a3b8; cursor: not-allowed; text-decoration: none;">
                                &laquo; ก่อนหน้า
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding: 6px 12px; font-size: 13px; border-radius: 6px; border: 1px solid var(--border); background: #ffffff; color: var(--text-main); text-decoration: none; transition: all 0.2s;">
                                &laquo; ก่อนหน้า
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link" style="padding: 6px 10px; font-size: 13px; border: none; background: transparent; color: #94a3b8;">{{ $element }}</span>
                            </li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link" style="padding: 6px 12px; font-size: 13px; font-weight: 600; border-radius: 6px; border: 1px solid var(--primary); background: var(--primary); color: #ffffff; box-shadow: 0 2px 6px var(--primary-glow);">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}" style="padding: 6px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; border: 1px solid var(--border); background: #ffffff; color: var(--text-main); text-decoration: none; transition: all 0.2s;">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding: 6px 12px; font-size: 13px; border-radius: 6px; border: 1px solid var(--border); background: #ffffff; color: var(--text-main); text-decoration: none; transition: all 0.2s;">
                                ถัดไป &raquo;
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" style="padding: 6px 12px; font-size: 13px; border-radius: 6px; border: 1px solid var(--border); background: #f8fafc; color: #94a3b8; cursor: not-allowed; text-decoration: none;">
                                ถัดไป &raquo;
                            </span>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </nav>
@endif
