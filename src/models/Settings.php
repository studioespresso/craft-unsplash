<?php
/**
 * Splashing Images plugin for Craft CMS 3.x
 *
 * unsplash.com integration for Craft 3
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\models;

use CraftCms\Cms\Plugin\PluginSettings;

/**
 * SplashingImages Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class Settings extends PluginSettings
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $destination = '';

    public string $folder = '';

    public string $pluginLabel = "Unsplash";

    // Public Methods
    // =========================================================================

    public function getRules(): array
    {
        return [
            'destination' => ['required', 'string'],
        ];
    }
}
