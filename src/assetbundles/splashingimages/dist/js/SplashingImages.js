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
        // If not saving, then proceed
        payload = {
            id: element.data('id')
        }
        payload[window.csrfTokenName] = window.csrfTokenValue;
        $.ajax({
            type: 'POST',
            url: Craft.getActionUrl('splashing-images/download'),
            dataType: 'JSON',
            data: payload,
            beforeSend: function () {
                console.log('posting..');
            },
            success: function (response) {

            },
            error: function (xhr, status, error) {

            }
        });

    });
});