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

$( document ).ready(function() {
    var $grid = jQuery('#splashing-container').masonry({
        itemSelector: 'img.item',
        gutter: 10,
    });
    $grid.imagesLoaded().progress( function() {
        $grid.masonry('');
    });
});