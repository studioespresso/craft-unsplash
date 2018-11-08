<?php
/**
 * Splashing Images plugin for Craft CMS 3.x
 *
 * unsplash.com integration for Craft 3
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages;

use craft\helpers\UrlHelper;
use studioespresso\splashingimages\services\SplashingImagesService as SplashingImagesServiceService;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\services\UserService;
use studioespresso\splashingimages\variables\SplashingImagesVariable;
use studioespresso\splashingimages\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 *
 * @property  SplashingImagesServiceService $splashingImagesService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class SplashingImages extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * SplashingImages::$plugin
     *
     * @var SplashingImages
     */
    public static $plugin;

    public function init()
    {
        parent::init();
        self::$plugin = $this;
        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['splashing-images'] = 'splashing-images/default/index';
                $event->rules['splashing-images/<page:\d+>'] = 'splashing-images/default/index';
                $event->rules['splashing-images/curated'] = 'splashing-images/default/curated';
                $event->rules['splashing-images/curated/<page:\d+>'] = 'splashing-images/default/curated';
                $event->rules['splashing-images/likes'] = 'splashing-images/default/likes';
                $event->rules['splashing-images/likes/<page:\d+>'] = 'splashing-images/default/likes/';
                $event->rules['splashing-images/find'] = 'splashing-images/default/find';
                $event->rules['splashing-images/search/<query>/<page:\d+>'] = 'splashing-images/default/search';
                $event->rules['splashing-images/oauth'] = 'splashing-images/default/oauth';
                $event->rules['splashing-images/oauth/disconnect'] = 'splashing-images/default/disconnect';
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin->id === $this->id) {
                    // Redirect to plugin settings
                    $request = Craft::$app->getRequest();
                    if ($request->isCpRequest) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl(
                            'settings/plugins/splashing-images'
                        ))->send();
                    }
                }
            }
        );

    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $navItem = [
            'label' => $this->getSettings()->pluginLabel ? $this->getSettings()->pluginLabel : 'Unsplash Images',
            'url' => $this->id,
        ];

        if (($iconPath = $this->cpNavIconPath()) !== null) {
            $navItem['icon'] = $iconPath;
        }

        return $navItem;
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        $volumes = Craft::$app->getVolumes();
        $destinationOptions[] = array('label' => '---', 'value' => "");
        foreach ($volumes->getAllVolumes() as $source) {
            $destinationOptions[] = array('label' => $source->name, 'value' => $source->id);
        }
        $user = false;
        if ($this->getSettings()->accessToken) {
            $userService = new UserService();
            $user = $userService->getUser();
        }
        return Craft::$app->view->renderTemplate(
            'splashing-images/settings',
            [
                'settings' => $this->getSettings(),
                'volumes' => $destinationOptions,
                'user' => $user,
            ]
        );
    }
}
