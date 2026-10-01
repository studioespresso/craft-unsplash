<?php

use CraftCms\Cms\Cms;
use CraftCms\Cms\Support\Facades\Plugins;
use CraftCms\Cms\Support\Facades\Volumes;
use Illuminate\Database\Migrations\Migration;
use studioespresso\splashingimages\SplashingImages;

return new class extends Migration {
    public function up(): void
    {
        if (!Cms::config()->allowAdminChanges) {
            return;
        }

        $plugin = SplashingImages::getInstance();
        $settings = $plugin->getSettings();

        if (is_numeric($settings->destination) && $volume = Volumes::getVolumeById((int)$settings->destination)) {
            $settings->destination = $volume->handle;
            Plugins::savePluginSettings($plugin, $settings->toArray());
        }
    }
};
