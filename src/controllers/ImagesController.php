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
use Illuminate\Http\Request;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\SplashingImages;

use function CraftCms\Cms\cp_url;
use function CraftCms\Cms\t;

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
        $images = array_values($data['images']);

        // The gallery fetches further pages as JSON while you scroll
        if ($request->wantsJson()) {
            return response()->json(['images' => $images, 'nextPageUrl' => $images ? $data['next_page'] : null]);
        }

        return new CpScreenResponse()
            ->title(SplashingImages::getInstance()->getSettings()->pluginLabel ?: 'Splashing Images')
            ->inertiaPage('splashing-images/Gallery', [
                'images' => $images,
                'nextPageUrl' => $images ? $data['next_page'] : null,
                'searchUrl' => cp_url('splashing-images/search'),
                'query' => $query,
                'labels' => [
                    'search' => t('Search', category: 'app'),
                    'save' => t('Save to Assets', category: 'splashing-images'),
                    'photoBy' => t('Photo by {name}', category: 'splashing-images'),
                    'end' => t('End of content', category: 'splashing-images'),
                    'empty' => t('No images found', category: 'splashing-images'),
                ],
                // The grid uses the full width
                'contentMaxWidth' => false,
            ]);
    }
}
