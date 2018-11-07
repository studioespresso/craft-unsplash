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

}
