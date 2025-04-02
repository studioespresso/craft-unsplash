<?php

namespace studioespresso\splashingimages\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\splashingimages\SplashingImages;

/**
 * m250402_174412_updateSettingsToHandle migration.
 */
class m250402_174412_updateSettingsToHandle extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if(Craft::$app->config->general->allowAdminChanges === false) {
            return true;
        }

        $settings = SplashingImages::getInstance()->settings;
        $destination = $settings->destination;

        if (is_numeric($destination)) {
            $volume = Craft::$app->volumes->getVolumeById($destination);

            if ($volume !== null) {
                $settings->destination = $volume->handle;
                Craft::$app->plugins->savePluginSettings(SplashingImages::getInstance(), $settings->toArray());
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250402_174412_updateSettingsToHandle cannot be reverted.\n";
        return false;
    }
}
