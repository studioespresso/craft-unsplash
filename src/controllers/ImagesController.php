<?php

/**
 * Splashing Images plugin for Craft CMS
 *
 * unsplash.com integration for Craft
 *
 * @link      https://studioespresso.co
 *
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\controllers;

use CraftCms\Cms\Http\Responses\CpScreenResponse;
use CraftCms\Cms\Support\Facades\HtmlStack;
use Illuminate\Http\Request;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\SplashingImages;

use function CraftCms\Cms\cp_url;
use function CraftCms\Cms\template;

/**
 * @author    Studio Espresso
 *
 * @since     1.0.0
 */
class ImagesController
{
    public function __construct(private UnsplashService $unsplash) {}

    public function index(Request $request, int $page = 1): mixed
    {
        return $this->respond($request, [
            'images' => $this->unsplash->getLatest($page),
            'next_page' => cp_url('splashing-images/'.($page + 1)),
        ]);
    }

    public function search(Request $request, int $page = 1): mixed
    {
        $query = (string) $request->query('search');
        if ($query === '') {
            return $this->index($request);
        }

        return $this->respond($request, [
            'images' => $this->unsplash->search($query, $page),
            'next_page' => cp_url('splashing-images/search/'.($page + 1), ['search' => $query]),
        ], $query);
    }

    private function respond(Request $request, array $data, ?string $query = null): mixed
    {
        // Infinite Scroll fetches the next page over XHR and only needs the images
        if ($request->ajax() && ! $request->inertia()) {
            return response(template('splashing-images/_includes/_images', ['data' => $data]));
        }

        $plugin = SplashingImages::getInstance();
        HtmlStack::cssFile($this->assetUrl('dist/css/SplashingImages.css'));
        foreach (['infinite-scroll.pkgd.min.js', 'masonry.pkgd.min.js', 'imagesloaded.pkgd.min.js', 'splashing-images.js'] as $script) {
            HtmlStack::jsFile($this->assetUrl("dist/js/$script"), ['defer' => true]);
        }

        return new CpScreenResponse()
            ->title($plugin->getSettings()->pluginLabel ?: 'Splashing Images')
            ->toolbarTemplate('splashing-images/_includes/_search', ['query' => $query])
            ->contentTemplate('splashing-images/_includes/_images', ['data' => $data])
            ->inertiaPage('cp/Screen');
    }

    /**
     * Published asset URL, cache-busted by the source file's modification time
     */
    private function assetUrl(string $path): string
    {
        $plugin = SplashingImages::getInstance();
        $version = filemtime($plugin->getResourcesPath().'/'.$path);

        return $plugin->asset($path)."?v=$version";
    }
}
