$(document).ready(function () {
    // init Masonry
    var $grid = $('.splashing-container').masonry({
        itemSelector: 'none', // select none at first
        columnWidth: '.splashing-image-sizer',
        gutter: 20,
        percentPosition: true,
        stagger: 20,
        // nicer reveal transition
        visibleStyle: { transform: 'translateY(0)', opacity: 1 },
        hiddenStyle: { transform: 'translateY(100px)', opacity: 0 },
    });

    $grid.imagesLoaded( function() {
        $grid.removeClass('are-images-unloaded');
        $grid.masonry( 'option', { itemSelector: '.splashing-image-grid' });
        var $items = $grid.find('.splashing-image-grid');
        $grid.masonry( 'appended', $items );
        $('.splashing-attribute').show();
    });


    function getNextUrl() {
    return $('.js-pagination__next').attr('href');
    }

    var msnry = $grid.data('masonry');
    console.log(msnry);
    $grid.infiniteScroll({
        path: getNextUrl,
        append: '.splashing-image-grid',
        outlayer: msnry,
        status: '.page-load-status',
    });

    $('.splashing-image').click(function (e) {
        var $image = $(this);

        payload = {}
        payload['id'] = $image.data('id');
        payload[window.csrfTokenName] = window.csrfTokenValue;

        $.ajax({
            type: 'POST',
            url: Craft.getActionUrl('splashing-images/download'),
            dataType: 'JSON',
            data: payload,
            beforeSend: function () {
                $image.parent().addClass('saving');
            },
            success: function (response) {
                $image.parent().removeClass('saving');
                Craft.cp.displayNotice(Craft.t('splashing-images', 'Image saved!'));
            },
            error: function (xhr, status, error) {
                $image.parent().removeClass('saving');
                Craft.cp.displayError(Craft.t('splashing-images', 'Oops, something went wrong...'));
            }
        });

    });
});