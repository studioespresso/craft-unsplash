<?php
/**
 * Splashing Images plugin for Craft CMS 3.x
 *
 * unsplash.com integration for Craft 3
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\services;

use Crew\Unsplash\HttpClient;
use Crew\Unsplash\Photo;
use Crew\Unsplash\Search;
use studioespresso\splashingimages\SplashingImages;

use Craft;
use craft\base\Component;

/**
 * SplashingImagesService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class UnsplashService extends Component
{

    public function __construct(array $config = [])
    {
        HttpClient::init([
            'applicationId' => 'f2f0833b9b95a11260cdbb20622e4990579254f787705ebe298cfdad4415198e',
            'utmSource' => 'Craft 3 Unsplash'
        ]);
    }

    public function registerDownload($id) {
        $photo = Photo::find($id);
        return $photo->download();

    }

    public function getCurated($count = 20)
    {
        if(Craft::$app->cache->get('splashing_curated')) {
            return Craft::$app->cache->get('splashing_curated');
        }
        $images = Photo::curated(1, $count);
        $images = $this->parseResults($images);
        Craft::$app->cache->add('splashing_curated', $images, 60*60*24);
        return $images;
    }

    public function getLatest($count = 20)
    {
        if(Craft::$app->cache->get('splashing_latest')) {
            return Craft::$app->cache->get('splashing_latest');
        }
        $images = Photo::all(1, $count);
        $images = $this->parseResults($images);
        Craft::$app->cache->add('splashing_latest', $images, 60*60*12);
        return $images;
    }

    public function search($query) {
        $images = Search::photos($query, 1, 20);
        foreach($images->getResults() as $image) {
            $data[$image['id']]['id'] = $image['id'];
            $data[$image['id']]['thumb'] = $image['urls']['thumb'];
            $data[$image['id']]['full'] = $image['urls']['full'];
        }
        return $data;
    }

    private function parseResults($images)
    {
        $data = [];
        foreach($images as $image) {
            $data[$image->id]['id'] = $image->id;
            $data[$image->id]['thumb'] = $image->urls['thumb'];
            $data[$image->id]['full'] = $image->urls['full'];
        }
        return $data;
    }
}
