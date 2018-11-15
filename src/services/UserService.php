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

use Crew\Unsplash\Collection;
use Crew\Unsplash\HttpClient;
use Crew\Unsplash\Photo;
use Crew\Unsplash\Search;
use Crew\Unsplash\User;
use studioespresso\splashingimages\records\UserRecord;
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
        $userRecord = UserRecord::findOne(['user' => Craft::$app->getUser()->id]);
        if ($userRecord) {
            HttpClient::init(
                [
                    'applicationId' => 'f2f0833b9b95a11260cdbb20622e4990579254f787705ebe298cfdad4415198e',
                    'utmSource' => 'Craft 3 Unsplash'
                ], [
                    'access_token' => $userRecord->token,
                    'expires_in' => 300000,
                ]
            );
        }
    }

    public function getUser()
    {
        return User::current();
    }

    public function saveToken($token)
    {
        $userRecord = new UserRecord();
        $userRecord->token = base64_decode($token);
        $userRecord->user = Craft::$app->getUser()->getId();
        return $userRecord->save();
    }

    public function removeToken()
    {
        $record = UserRecord::findOne(['user' => Craft::$app->getUser()->getId()]);
        return $record->delete();
    }

    public function getLikes($page, $count = 30)
    {
        if (Craft::$app->cache->get('splashing_likes_' . $page)) {
            return Craft::$app->cache->get('splashing_likes_' . $page);
        }
        $images = User::current()->likes($page, $count);

        $data['images'] = $this->parseResults($images);
        $data['next_page'] = $this->getNextUrl();
        $data['hasUser'] = $this->getUser();
        Craft::$app->cache->add('splashing_likes_' . $page, $data, 60 * 60 * 24);
        return $data;
    }

    public function getCollections($page, $count = 30)
    {

        $collections = User::current()->collections($page, $count);
        if($collections) {
            foreach($collections as $collection) {
                $data['collections'][$collection->id]['id'] = $collection->id;
                $data['collections'][$collection->id]['title'] = $collection->title;
                $data['collections'][$collection->id]['cover'] = $collection->cover_photo;
            }
            $data['next_page'] = $this->getNextUrl();
            $data['hasUser'] = $this->getUser();
            return $data;
        }
    }

    public function getCollection($collection, $page, $count = 30)
    {
        $images = Collection::find($collection)->photos($page, $count);
        $data['images'] = $this->parseResults($images);
        $data['next_page'] = $this->getNextUrl();
        $data['hasUser'] = $this->getUser();
        return $data;
    }

    private function getNextUrl()
    {
        $segments = Craft::$app->request->getSegments();
        if (count($segments) > 2) {
            $segments[count($segments) - 1] = $segments[count($segments) - 1] + 1;
        } else {
            $segments[count($segments)] = 2;
        }
        return implode('/', $segments);
    }

    private function parseResults($images)
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
