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

use studioespresso\splashingimages\services\UnsplashService;

use Craft;
use craft\web\Controller;

/**
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class DefaultController extends Controller
{
    /**
     * @var
     */
    public $unsplash;

    /**
     * Spin up the Unsplash service
     */
    public function init()
    {
        $this->unsplash = new UnsplashService();
    }

    /**
     * Render the plugins main page and show the latest Unsplash images
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $images = $this->unsplash->getLatest();
        return $this->renderTemplate('splashing-images/_index', ['images' => $images]);
    }

    /**
     * Renders an overview of curated images from Unsplash
     * @return \yii\web\Response
     */
    public function actionCurated()
    {
        $images = $this->unsplash->getCurated();
        return $this->renderTemplate('splashing-images/_curated', ['images' => $images]);
    }

    /**
     * Handles searching & returning images from Unsplash
     * @param $query string
     * @param $page int
     * @return bool|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionSearch($query, $page)
    {
        if (!$query) {
            return false;
        }
        $images = $this->unsplash->search($query, $page);
        return $this->renderTemplate('splashing-images/_search', $images);
    }

}
