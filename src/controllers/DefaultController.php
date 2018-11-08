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
use studioespresso\splashingimages\services\UserService;
use studioespresso\splashingimages\SplashingImages;

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
     * @var
     */
    private $userService;

    /**
     * Spin up the Unsplash service
     */
    public function init()
    {
        $this->unsplash = new UnsplashService();
        if(SplashingImages::$plugin->getSettings()->accessToken) {
            $this->userService = new UserService();
        }
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
     * Renders an overview of curated images from Unsplash
     * @param $page int
     * @return \yii\web\Response
     */
    public function actionCurated($page = 1)
    {
        $data = $this->unsplash->getCurated($page);
        return $this->renderTemplate('splashing-images/_curated', $data);
    }


    /**
     * Redirect search form submit to correct results url
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFind() {
        $resultsPage = Craft::$app->request->getRequiredBodyParam('redirect');
        $query = Craft::$app->request->getRequiredBodyParam('query');
        $this->redirect($resultsPage . '/' . $query . '/1');
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
        $data = $this->unsplash->search($query, $page);
        return $this->renderTemplate('splashing-images/_search', $data);
    }

    public function actionLikes($page = 1) {
        $data = $this->userService->getLikes($page);
        return $this->renderTemplate('splashing-images/_likes', $data);

    }

    public function actionOauth() {
        $session = Craft::$app->request->getRequiredQueryParam('session');
        $settings = SplashingImages::$plugin->getSettings();
        $settings->accessToken = base64_decode($session);
        Craft::$app->plugins->savePluginSettings(SplashingImages::$plugin, $settings->toArray());
        Craft::$app->session->setNotice(Craft::t('splashing-images', 'Unsplash account linked successfully'));
        $this->redirect('settings/plugins/splashing-images');
    }

    public function actionDisconnect() {
        $settings = SplashingImages::$plugin->getSettings();
        $settings->accessToken = null;
        Craft::$app->plugins->savePluginSettings(SplashingImages::$plugin, $settings->toArray());
        Craft::$app->session->setNotice(Craft::t('splashing-images', 'Removed Unsplash account'));
        $this->redirect('settings/plugins/splashing-images');
    }

}
