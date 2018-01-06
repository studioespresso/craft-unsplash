/**
 * Splashing Images plugin for Craft CMS
 *
 * Splashing Images JS
 *
 * @author    Studio Espresso
 * @copyright Copyright (c) 2017 Studio Espresso
 * @link      https://studioespresso.co
 * @package   SplashingImages
 * @since     1.0.0
 */

$(document).ready(function () {
    var grid = jQuery('#splashing-container').masonry({
        itemSelector: 'div.splashing',
        gutter: 10,
    });
    grid.imagesLoaded().progress(function () {
        grid.masonry();
    });
});

$(document).ready(function ($) {
    var container = $('#splashing-container');

    $.LoadingOverlaySetup({
        color           : "rgba(241,241,241,0.5)",
        maxSize         : "80px",
        minSize         : "20px",
        resizeInterval  : 0,
        size            : "30%"
    });

    $('div.splashing').click(function (e) {
        var element = $(this);

        payload = {
            id: element.data('id')
        }
        payload[window.csrfTokenName] = window.csrfTokenValue;

        if (!element.hasClass('saving')) {
            element.addClass('saving');
            $.ajax({
                type: 'POST',
                url: Craft.getActionUrl('splashing-images/download'),
                dataType: 'JSON',
                data: payload,
                beforeSend: function () {
                    element.LoadingOverlay("show");
                    console.log('posting..');
                },
                success: function (response) {
                    console.log(response);
                    element.LoadingOverlay("hide");
                    Craft.cp.displayNotice(Craft.t('splashing-images','Image saved!'));
                },
                error: function (xhr, status, error) {
                    element.LoadingOverlay("hide");
                    Craft.cp.displayError(Craft.t('splashing-images', 'Oops, something went wrong...'));
                }
            });
        }
    });
});