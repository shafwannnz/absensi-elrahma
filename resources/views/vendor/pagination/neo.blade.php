{{-- Pagination bergaya neobrutalism. Dipanggil: $items->links('vendor.pagination.neo') --}}
@if ($paginator->hasPages())
    <nav class="mt-5 flex flex-wrap items-center justify-between gap-3" role="navigation" aria-label="Navigasi halaman">
        <p class="font-bold">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="nb-btn nb-btn-disabled">Sebelumnya</span>
            @else
                <a class="nb-btn bg-white" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="nb-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else
                <span class="nb-btn nb-btn-disabled">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
