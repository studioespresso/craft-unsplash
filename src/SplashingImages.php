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

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\UrlManager;
use studioespresso\splashingimages\models\Settings;
use studioespresso\splashingimages\services\SplashingImagesService as SplashingImagesServiceService;
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

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['splashing-images'] = 'splashing-images/default/index';
                $event->rules['splashing-images/<page:\d+>'] = 'splashing-images/default/index';
                $event->rules['splashing-images/curated'] = 'splashing-images/default/curated';
                $event->rules['splashing-images/curated/<page:\d+>'] = 'splashing-images/default/curated';
                $event->rules['splashing-images/likes'] = 'splashing-images/default/likes';
                $event->rules['splashing-images/likes/<page:\d+>'] = 'splashing-images/default/likes';
                $event->rules['splashing-images/collections'] = 'splashing-images/default/collections';
                $event->rules['splashing-images/collections/<collection:\d+>'] = 'splashing-images/default/collection';
                $event->rules['splashing-images/find'] = 'splashing-images/default/find';
                $event->rules['splashing-images/search'] = 'splashing-images/default/search';
                $event->rules['splashing-images/search/<query>/<page:\d+>'] = 'splashing-images/default/search';
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
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
    protected function createSettingsModel(): Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): array
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
        foreach ($volumes->getAllVolumes() as $source) {
            $destinationOptions[] = array('label' => $source->name, 'value' => $source->id);
        }
        return Craft::$app->view->renderTemplate(
            'splashing-images/settings',
            [
                'settings' => $this->getSettings(),
                'volumes' => $destinationOptions ?? null,
            ]
        );
    }
}
