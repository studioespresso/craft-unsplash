$(document).ready(function () {
    var $grid = $('.splashing-container').imagesLoaded( function() {
        // init Masonry after all images have loaded
        $grid.masonry({
            itemSelector: '.splashing-image-grid',
            columnWidth: '.splashing-image-grid',
            percentPosition: true,
            gutter: 20,
            stagger: 2
        });
    });

    $('.splashing-image').click(function (e) {
        var $image = $(this);
        $image.parent().addClass('saving');

        payload = {
            id: $image.data('id')
        }
        payload[window.csrfTokenName] = window.csrfTokenValue;
        $.ajax({
            type: 'POST',
            url: Craft.getActionUrl('splashing-images/download'),
            dataType: 'JSON',
            data: payload,
            beforeSend: function () {
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