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
use craft\errors\InvalidSubpathException;
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
class DownloadController extends Controller
{

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/splashing-images/default
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if (!Craft::$app->request->isAjax) {
            return false;
        }

        $path = Craft::$app->getPath();
        $dir = $path->getTempPath() . '/unsplash/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $assets = Craft::$app->getAssets();
        $settings = SplashingImages::$plugin->getSettings();
        if(!isset($settings->destination)) {
            $returnData['success'] = false;
            $returnData['message'] = Craft::t('splashing-images', 'Please set a file destination in settings so images can be saved');
            return $this->asJson($returnData);
        }

        $id = Craft::$app->request->post('id');
        $unplash = new UnsplashService();
        $photo = $unplash->getPhoto($id);
        $payload = $photo->download();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $payload);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $picture = curl_exec($ch);
        curl_close($ch);


        $tmpImage = 'photo-' . rand() . '.jpg';
        $tempPath = $dir . $tmpImage;
        $saved = file_put_contents($tempPath, $picture);

        $volume = Craft::$app->volumes->getVolumeById($settings->destination);

        $subpath = (string)SplashingImages::$plugin->getSettings()->folder;

        if ($subpath) {
            try {
                $subpath = Craft::$app->getView()->renderObjectTemplate($subpath, $settings);
            } catch (\Throwable $e) {
                throw new InvalidSubpathException($subpath);
            }
        }
        $assetsService = Craft::$app->getAssets();

        $folderId = $assetsService->ensureFolderByFullPathAndVolume($subpath, $volume);

        $asset = new Asset();
        $asset->tempFilePath = $tempPath;
        $asset->filename = $tmpImage;
        $asset->newFolderId = $folderId;
        $asset->volumeId = $volume->id;
        $asset->title = 'Photo by ' . $photo->photographer()->name;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        $result = Craft::$app->elements->saveElement($asset);

        if ($result) {
            $returnData['success'] = true;
            $returnData['message'] = Craft::t('splashing-images', 'Image saved!');
        } else {
            $returnData['success'] = false;
            $returnData['message'] = Craft::t('splashing-images', 'Oops, something went wrong...');
        }
        return $this->asJson($returnData);
        exit;

    }

}
