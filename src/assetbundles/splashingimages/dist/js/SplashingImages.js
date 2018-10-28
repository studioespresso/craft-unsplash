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
        itemSelector: '.splashing-image-grid',
        columnWidth: '.splashing-image-grid',
        percentPosition: true,
        gutter: 20,
        stagger: 2
    });
    grid.imagesLoaded().progress(function () {
        grid.masonry();
    });
});

$(document).ready(function ($) {
    var container = $('#splashing-container');
    $('div.splashing-image').click(function (e) {
        var $element = $(this);
        $element.parent().addClass('saving');

        payload = {
            id: $element.data('id')
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
                console.log(response);
                $element.parent().removeClass('saving');
                Craft.cp.displayNotice(Craft.t('splashing-images', 'Image saved!'));
            },
            error: function (xhr, status, error) {
                Craft.cp.displayError(Craft.t('splashing-images', 'Oops, something went wrong...'));
            }
        });

    });
});