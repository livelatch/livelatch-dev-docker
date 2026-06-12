<div class="ll-skeleton-table-wrap" aria-hidden="true">
    <div class="ll-skeleton ll-skeleton-heading"></div>
    <div class="ll-skeleton-table">
        <div class="ll-skeleton-table-row ll-skeleton-table-head">
            <div class="ll-skeleton ll-skeleton-line"></div>
            <div class="ll-skeleton ll-skeleton-line"></div>
            <div class="ll-skeleton ll-skeleton-line"></div>
            <div class="ll-skeleton ll-skeleton-line"></div>
        </div>
        @for($i = 0; $i < 5; $i++)
            <div class="ll-skeleton-table-row">
                <div class="ll-skeleton ll-skeleton-line"></div>
                <div class="ll-skeleton ll-skeleton-line ll-skeleton-line-wide"></div>
                <div class="ll-skeleton ll-skeleton-line ll-skeleton-line-mid"></div>
                <div class="ll-skeleton ll-skeleton-pill ll-skeleton-pill-short"></div>
            </div>
        @endfor
    </div>
</div>
