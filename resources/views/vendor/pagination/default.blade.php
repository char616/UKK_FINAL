@if ($paginator->hasPages())
    <style>
        .scp-pagination-wrapper {
            margin: 1rem 0;
            padding: 8px 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .scp-pagination-wrapper .pagination {
            display: inline-flex !important;
            flex-wrap: wrap !important;
            gap: 3px !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            align-items: center !important;
        }
        .scp-pagination-wrapper .page-item .page-link {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 28px !important;
            height: 28px !important;
            padding: 0 6px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            text-decoration: none !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 6px !important;
            line-height: 26px !important;
            transition: all 0.15s ease !important;
        }
        .scp-pagination-wrapper .page-item.active .page-link {
            z-index: 3 !important;
            color: #ffffff !important;
            background-color: #174ea6 !important;
            border-color: #174ea6 !important;
            box-shadow: 0 2px 4px rgba(23, 78, 166, 0.2) !important;
        }
        .scp-pagination-wrapper .page-item.disabled .page-link {
            color: #94a3b8 !important;
            pointer-events: none !important;
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            opacity: 0.65 !important;
        }
        .scp-pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
            color: #174ea6 !important;
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
        }
        .scp-pagination-wrapper svg,
        .pagination svg,
        nav svg,
        svg.w-5,
        svg.h-5 {
            width: 12px !important;
            height: 12px !important;
            max-width: 12px !important;
            max-height: 12px !important;
            min-width: 0 !important;
            min-height: 0 !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        @media (max-width: 576px) {
            .scp-pagination-wrapper {
                padding: 8px 10px !important;
                gap: 8px !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
            }
            .scp-pagination-wrapper .pagination {
                gap: 2px !important;
                justify-content: center !important;
            }
            .scp-pagination-wrapper .page-item .page-link {
                min-width: 26px !important;
                height: 26px !important;
                padding: 0 4px !important;
                font-size: 10.5px !important;
                line-height: 24px !important;
                border-radius: 5px !important;
            }
        }
    </style>

    <nav class="scp-pagination-wrapper d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2" aria-label="Navigasi Halaman">
        <div class="text-muted small" style="font-size: 11px;">
            Menampilkan <span class="fw-bold text-dark">{{ $paginator->firstItem() }}</span>-{{ $paginator->lastItem() }} dari <span class="fw-bold text-dark">{{ $paginator->total() }}</span> data
        </div>

        <div>
            <ul class="pagination pagination-sm m-0">
                {{-- Tombol Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="Sebelumnya">
                        <span class="page-link" title="Sebelumnya">
                            <i class="bi bi-chevron-left" style="font-size: 10px;"></i>
                            <span class="d-none d-sm-inline ms-1">Prev</span>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" title="Sebelumnya">
                            <i class="bi bi-chevron-left" style="font-size: 10px;"></i>
                            <span class="d-none d-sm-inline ms-1">Prev</span>
                        </a>
                    </li>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($elements as $element)
                    {{-- Pemisah Tiga Titik --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Link Nomor Halaman --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Tombol Selanjutnya --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Selanjutnya" title="Selanjutnya">
                            <span class="d-none d-sm-inline me-1">Next</span>
                            <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="Selanjutnya">
                        <span class="page-link" title="Selanjutnya">
                            <span class="d-none d-sm-inline me-1">Next</span>
                            <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
