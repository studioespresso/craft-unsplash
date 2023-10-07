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
use craft\elements\Asset;
use craft\errors\InvalidSubpathException;
use craft\web\Controller;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\SplashingImages;

/**
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class DownloadController extends Controller
{
    public function actionIndex(): \yii\web\Response|false
    {
        if (!Craft::$app->request->isAjax) {
            return false;
        }

        $path = Craft::$app->getPath();
        $dir = $path->getTempPath() . '/unsplash/';
        if (!is_dir($dir)) {
            if (!mkdir($dir) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
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

        $folder = $assetsService->ensureFolderByFullPathAndVolume($subpath, $volume);

        $asset = new Asset();
        $asset->tempFilePath = $tempPath;
        $asset->filename = $tmpImage;
        if($photo->description) {
            $asset->alt = $photo->description;
        }
        $asset->newFolderId = $folder->id;
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
