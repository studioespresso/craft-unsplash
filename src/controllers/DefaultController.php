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

use craft\helpers\UrlHelper;
use studioespresso\splashingimages\records\UserRecord;
use studioespresso\splashingimages\services\UnsplashService;

use Craft;
use craft\web\Controller;
use studioespresso\splashingimages\services\UserService;
use studioespresso\splashingimages\SplashingImages;
use yii\web\NotFoundHttpException;

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
    private $unsplash;

    /**
     * Spin up the Unsplash service
     */
    public function init(): void
    {
        $this->unsplash = new UnsplashService();
        parent::init();
    }

    /**
     * Render the plugins main page and show the latest Unsplash images
     * @param $page int
     * @return \yii\web\Response
     */
    public function actionIndex($page = 1)
    {
        $data = $this->unsplash->getLatest($page);
        return $this->renderTemplate('splashing-images/_index', $data);
    }

    /**
     * Redirect search form submit to correct results url
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFind()
    {
        $query = Craft::$app->request->getRequiredBodyParam('query');
        return $this->redirect(UrlHelper::cpUrl('splashing-images/search/1', ['search' => $query]));
    }

    /**
     * Handles searching & returning images from Unsplash
     * @param $query string
     * @param $page int
     * @return bool|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionSearch(int $page)
    {
        $query = $this->request->getRequiredQueryParam('search');
        if (!$query) {
            return false;
        }
        $data = $this->unsplash->search($query, $page);
        return $this->renderTemplate('splashing-images/_search', $data);
    }

}
