<?php
/**
 * Splashing Images plugin for Craft CMS
 *
 * unsplash.com integration for Craft
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\services;

use Illuminate\Support\Facades\Cache;
use Unsplash\HttpClient;
use Unsplash\Photo;
use Unsplash\Search;

/**
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class UnsplashService
{
    public function __construct()
    {
        HttpClient::init([
            'applicationId' => 'f2f0833b9b95a11260cdbb20622e4990579254f787705ebe298cfdad4415198e',
            'utmSource' => 'Craft 3 Unsplash',
        ]);
    }

    public function getPhoto($id): Photo
    {
        return Photo::find($id);
    }

    public function getLatest(int $page, int $count = 30): array
    {
        return Cache::remember('splashing_latest_' . $page, 60 * 60 * 12, fn() => $this->parseResults(Photo::all($page, $count)));
    }

    public function search(string $query, int $page = 1, int $count = 30): array
    {
        return Cache::remember('splashing_' . md5($query) . '_' . $page, 60 * 60 * 24, fn() => $this->parseResults(Search::photos($query, $page, $count)->getArrayObject()));
    }

    private function parseResults($images): array
    {
        $data = [];
        foreach ($images as $image) {
            $data[$image->id]['id'] = $image->id;
            $data[$image->id]['thumb'] = $image->urls['thumb'];
            $data[$image->id]['small'] = $image->urls['small'];
            $data[$image->id]['full'] = $image->urls['full'];
            $data[$image->id]['attr']['name'] = $image->user['name'];
            $data[$image->id]['attr']['link'] = $image->user['links']['html'];
        }
        return $data;
    }
}
