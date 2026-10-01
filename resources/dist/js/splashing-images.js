// The Craft 6 CP renders page content with Vue, so the grid can appear after this script runs
function initSplashingGrid() {
    var $container = $('.splashing-container:not(.is-initialized)');
    if (!$container.length || !$.fn.masonry || !$.fn.imagesLoaded || !$.fn.infiniteScroll) {
        return;
    }
    $container.addClass('is-initialized');

    var $grid = $container.masonry({
        itemSelector: 'none', // select none at first
        columnWidth: '.splashing-image-sizer',
        gutter: 16,
        percentPosition: true,
        visibleStyle: {transform: 'translateY(0)', opacity: 1},
        hiddenStyle: {transform: 'translateY(10px)', opacity: 0},
        transitionDuration: '0.4s'
    });

    $grid.imagesLoaded(function () {
        $grid.removeClass('are-images-unloaded');
        $grid.masonry('option', {itemSelector: '.splashing-image-grid'});
        var $items = $grid.find('.splashing-image-grid');
        $grid.masonry('appended', $items);
    });

    var msnry = $grid.data('masonry');

    if($('.js-pagination__next').length) {
        $grid.infiniteScroll({
            scrollThreshold: 200,
            path: '.js-pagination__next',
            append: '.splashing-image-grid',
            outlayer: msnry,
            status: '.page-load-status',
            hideNav: '.pagination',
            historyTitle: true,
            history: 'push',
            debug: false,
        });
    }

}

new MutationObserver(initSplashingGrid).observe(document.body, {childList: true, subtree: true});
// Scripts injected on Inertia visits load in any order, so retry as each one finishes
document.addEventListener('load', initSplashingGrid, true);
initSplashingGrid();

$(function () {
    $(document).on('click', '.js-splashing-image', function (e) {
        var $image = $(this);

        payload = {}
        payload['id'] = $image.data('id');
        payload[Craft.csrfTokenName] = Craft.csrfTokenValue;

        $.ajax({
            type: 'POST',
            url: Craft.getCpUrl('splashing-images/download'),
            dataType: 'JSON',
            data: payload,
            beforeSend: function () {
                $image.parent().addClass('saving');
            },
            success: function (response) {
                $image.parent().removeClass('saving');
                console.log(response);
                if (response.success) {
                    Craft.cp.displayNotice(response.message);
                } else {
                    Craft.cp.displayError(response.message);
                }
            },
            error: function (xhr, status, error) {
                $image.parent().removeClass('saving');
                Craft.cp.displayError(xhr.responseJSON?.message ?? error);
            }
        });
    });
});
