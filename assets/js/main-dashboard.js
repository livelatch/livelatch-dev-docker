
(function ($) {

    "use strict";

    var fullHeight = function () {

        $('.js-fullheight').css('height', $(window).height());
        $(window).resize(function () {
            $('.js-fullheight').css('height', $(window).height());
        });

    };
    fullHeight();

    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // NOTE: link-reordering (Sortable on #links-table-body) is initialised by
    // LivelatchLinksManager in resources/views/studio/links.blade.php. The
    // legacy Sortable init that used to live here was removed: it double-bound
    // the same element, treated window.linksTableOrders (now a JSON array) as a
    // pipe-delimited string — throwing "order.split is not a function" — and
    // poked a since-removed #frPreview2 iframe. Do not re-add it here.

})(jQuery);
