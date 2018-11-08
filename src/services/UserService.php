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
use Crew\Unsplash\User;
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
class UserService extends Component
{

    public function __construct(array $config = [])
    {
        HttpClient::init(
            [
            'applicationId' => 'f2f0833b9b95a11260cdbb20622e4990579254f787705ebe298cfdad4415198e',
            'utmSource' => 'Craft 3 Unsplash'
            ], [
                'access_token' => SplashingImages::$plugin->getSettings()->accessToken,
                'expires_in' => 300000,
            ]
        );
    }

    public function getUser()
    {
        return User::current();
    }

    public function getLikes($page, $count = 30) {
        if(Craft::$app->cache->get('splashing_likes_'.$page)) {
            return Craft::$app->cache->get('splashing_likes_'.$page);
        }
        $images = User::current()->likes($page, $count);

        $data['images'] = $this->parseResults($images);
        $data['next_page'] = $this->getNextUrl();
        $data['user'] = true;
        Craft::$app->cache->add('splashing_likes_'.$page, $data, 60*60*24);
        return $data;
    }

    private function getNextUrl() {
        $segments  = Craft::$app->request->getSegments();
        if(count($segments) > 2) {
            $segments[count($segments)-1] = $segments[count($segments)-1] +1;
        } else {
            $segments[count($segments)] = 2;
        }
        return implode('/', $segments);
    }

    private function parseResults($images)
    {
        $data = [];
        foreach($images as $image) {
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
