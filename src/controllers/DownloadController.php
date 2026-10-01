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

use CraftCms\Cms\Asset\Elements\Asset;
use CraftCms\Cms\Asset\Validation\AssetRules;
use CraftCms\Cms\Support\Facades\Elements;
use CraftCms\Cms\Support\Facades\Folders;
use CraftCms\Cms\Support\Facades\Template;
use CraftCms\Cms\Support\Facades\Volumes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\SplashingImages;

use function CraftCms\Cms\t;

/**
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class DownloadController
{
    public function __invoke(Request $request, UnsplashService $unsplash): JsonResponse
    {
        $settings = SplashingImages::getInstance()->getSettings();
        $volume = $settings->destination ? Volumes::getVolumeByHandle($settings->destination) : null;
        if (!$volume) {
            return $this->result(false, 'Please set a file destination in settings so images can be saved');
        }

        $photo = $unsplash->getPhoto($request->input('id'));
        $tempPath = tempnam(sys_get_temp_dir(), 'unsplash') . '.jpg';
        Http::sink($tempPath)->get($photo->download())->throw();

        $subpath = $settings->folder ? Template::renderObjectTemplate($settings->folder, $settings) : '';

        $asset = new Asset();
        $asset->tempFilePath = $tempPath;
        $asset->filename = 'photo-' . $photo->id . '.jpg';
        /** @phpstan-ignore-next-line */
        if ($photo->description) {
            $asset->alt = $photo->description;
        }
        $asset->newFolderId = Folders::ensureFolderByFullPathAndVolume($subpath, $volume)->id;
        $asset->volumeId = $volume->id;
        /** @phpstan-ignore-next-line */
        $asset->title = 'Photo by ' . $photo->photographer()->name;
        $asset->avoidFilenameConflicts = true;
        $asset->ruleset->useScenario(AssetRules::SCENARIO_CREATE);

        return Elements::saveElement($asset)
            ? $this->result(true, 'Image saved!')
            : $this->result(false, 'Oops, something went wrong...');
    }

    private function result(bool $success, string $message): JsonResponse
    {
        return new JsonResponse(['success' => $success, 'message' => t($message, category: 'splashing-images')]);
    }
}
