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
        itemSelector: 'img.item',
        gutter: 10,
    });
    grid.imagesLoaded().progress(function () {
        grid.masonry();
    });
});

$(document).ready(function ($) {
    var container = $('#splashing-container');

    $('img.item').click(function (e) {

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
                    console.log('posting..');
                },
                success: function (response) {
                    console.log(response);
                    Craft.cp.displayNotice(Craft.t('Image saved!'));
                },
                error: function (xhr, status, error) {
                    Craft.cp.displayError(Craft.t('Oops, something went wrong...'));
                }
            });
        }
    });
});