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

namespace studioespresso\splashingimages;

use CraftCms\Cms\Asset\Data\Volume;
use CraftCms\Cms\Cp\Data\NavItem;
use CraftCms\Cms\Form\Controls\Choice;
use CraftCms\Cms\Form\Controls\Text;
use CraftCms\Cms\Form\Form;
use CraftCms\Cms\Form\FormContext;
use CraftCms\Cms\Form\Nodes\Field;
use CraftCms\Cms\Plugin\Plugin;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Support\Facades\Volumes;
use studioespresso\splashingimages\models\Settings;

use function CraftCms\Cms\t;

/**
 * @author    Studio Espresso
 *
 * @since     1.0.0
 *
 * @method Settings getSettings()
 */
class SplashingImages extends Plugin
{
    protected array $publishables = [
        __DIR__.'/../resources/dist' => 'dist',
    ];

    protected static function createSettings(): ?PluginSettings
    {
        return new Settings;
    }

    public function getCpNavItem(): NavItem|array|null
    {
        return new NavItem()
            ->label($this->getSettings()->pluginLabel ?: 'Unsplash Images')
            ->href($this->handle)
            ->iconSvg(file_get_contents($this->getBasePath().'/icon-mask.svg'));
    }

    public function settingsForm(FormContext $context = new FormContext): ?Form
    {
        $volumes = Volumes::getAllVolumes()
            ->map(fn (Volume $volume) => ['label' => $volume->name, 'value' => $volume->handle])
            ->values()
            ->all();

        return Form::make([
            Field::make(t('Plugin label', category: 'splashing-images'), Text::make('pluginLabel'))
                ->instructions(t('You can give the plugin in different name in the CP navigation here', category: 'splashing-images')),
            Field::make(t('Volume', category: 'splashing-images'), Choice::make('destination')->options($volumes)->placeholder(t('Select a volume', category: 'splashing-images')))
                ->instructions(t('Select the volume where your images should be save', category: 'splashing-images'))
                ->required(),
            Field::make(t('Folder', category: 'splashing-images'), Text::make('folder'))
                ->instructions('path/to/subfolder'),
        ]);
    }
}
