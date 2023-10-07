<?php
/**
 * Splashing Images plugin for Craft CMS
 *
 * unsplash.com integration for Craft
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use studioespresso\splashingimages\services\UnsplashService;
use yii\web\Response;

/**
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    /**
     * @var UnsplashService
     */
    private UnsplashService $unsplash;

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
     * @return Response
     */
    public function actionIndex($page = 1): Response
    {
        $data = $this->unsplash->getLatest($page);
        return $this->renderTemplate('splashing-images/_index', [
            'data' => $data
        ]);
    }

    /**
     * Redirect search form submit to correct results url
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFind(): Response
    {
        $query = Craft::$app->request->getRequiredBodyParam('query');
        return $this->redirect(UrlHelper::cpUrl('splashing-images/search' . '/' . $query . '/1'));
    }

    /**
     * Handles searching & returning images from Unsplash
     * @param $query string
     * @param $page int
     * @return bool|Response
     */
    public function actionSearch(string $query, int $page): bool|Response
    {
        if (!$query) {
            return false;
        }
        $data = $this->unsplash->search($query, $page);
        return $this->renderTemplate('splashing-images/_index', [
            'query' => $query,
            'data' => $data
        ]);
    }

}
