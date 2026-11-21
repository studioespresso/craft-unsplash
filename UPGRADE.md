# Upgrading to Splashing Images 6 (Craft 6)

This guide lists what you need to update when moving from Splashing Images 5.x (Craft 5) to 6.x.

## Requirements

- Craft CMS 6
- PHP 8.5
- Splashing Images **5.0.4** on Craft 5 before upgrading. It converts the volume setting from an ID to a handle; 6.x refuses to update from an older version.
- `craftcms/yii2-adapter` is not needed

The plugin handle (`splashing-images`) and its settings are unchanged.

## Breaking changes

### Classes and namespaces

Classes moved to Laravel-style folders. The root namespace (`studioespresso\splashingimages`) is unchanged.

| Before | After |
| --- | --- |
| `studioespresso\splashingimages\models\Settings` | `studioespresso\splashingimages\Settings` |
| `studioespresso\splashingimages\services\UnsplashService` | `studioespresso\splashingimages\Services\UnsplashService` |
| `studioespresso\splashingimages\controllers\DefaultController` | `studioespresso\splashingimages\Http\Controllers\ImagesController` |
| `studioespresso\splashingimages\controllers\DownloadController` | `studioespresso\splashingimages\Http\Controllers\DownloadController` |

The static `SplashingImages::$plugin` is gone: use `SplashingImages::getInstance()`. For the service, resolve it from the container: `app(UnsplashService::class)`.

### Control panel URLs

`splashing-images/find` (the search form's POST target) is gone. Search is a GET request: `splashing-images/search?search=…`. The other control panel URLs are unchanged.

## Other changes

- The image grid is a Vue page now; it loads more images as you scroll, as before.
- Installing the plugin no longer redirects to its settings page.
