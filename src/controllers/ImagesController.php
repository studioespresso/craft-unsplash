<?php
/**
 * Splashing Images plugin for Craft CMS 3.x
 *
 * unsplash.com integration for Craft 3
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\controllers;

use craft\elements\Asset;
use craft\services\Path;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\SplashingImages;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class ImagesController extends Controller
{

    public function actionIndex() {
        $unsplashService = new UnsplashService();
        $images = $unsplashService->getCurated();
        $data = $this->prepData($images);
        return $this->renderTemplate('splashing-images/_index', $data);

    }

    public function actionLatest() {
        $unsplashService = new UnsplashService();
        $images = $unsplashService->getLatest();
        $data = $this->prepData($images);

        return $this->renderTemplate('splashing-images/_latest', $data);
    }

    public function actionSearch() {
        if(!Craft::$app->request->get('q')) {
            return false;
        }
        $query = Craft::$app->request->get('q');
        $unsplash = new UnsplashService();
        $images = $unsplash->search($query);

        $this->view->setTemplateMode('cp');
        return $this->renderTemplate('splashing-images/_search', ['images' => $images]);
    }

    private function prepData($images) {
        $data['images'] = $images;
        if(Craft::$app->cache->get('splashing_last_search')) {
            $data['lastSearch'] = Craft::$app->cache->get('splashing_last_search');
        }
        return $data;
    }

}
