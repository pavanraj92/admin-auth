@if ($paginator->hasPages())
    <div class="card-body">
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
                {{-- Previous Page Link --}}
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() ?? '#' }}" aria-label="Previous" tabindex="{{ $paginator->onFirstPage() ? '-1' : '0' }}">
                        <span aria-hidden="true">«</span>
                    </a>
                </li>
                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    @endif
                @endforeach
                {{-- Next Page Link --}}
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() ?? '#' }}" aria-label="Next" tabindex="{{ $paginator->hasMorePages() ? '0' : '-1' }}">
                        <span aria-hidden="true">»</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
@endif
