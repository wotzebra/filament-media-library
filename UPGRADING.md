# Upgrading

## From v3 to v4

- Install `wotz/filament-media-library` instead of `codedor/filament-media-library`
- Replace all occurrences of `Codedor\MediaLibrary` namespace with new `Wotz\MediaLibrary` namespace

## From v2 to v3

### Video not allowed anymore

The video config is removed in the `filament-media-library.extensions` config, if you need it in your project and config is not published. Publish the config (`php artisan vendor:publish --tag=filament-media-library-config`) and add the video config to the extensions array.

```php
return [
    'extensions' => [
        // ...
        'video' => [
            'mp4',
            'm4v',
            'webm',
            'ogg',
        ],
    ],
];
```

### WebP by default

By default, we will only generate webp formats (so no more jpeg, png etc.). So make sure you do not reference to a jpg or png in your code. You should just use our `getFormatOrOriginal()` method on the Attachment model.

### Picture component

This has been rewritten to use the native browser [lazy](https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/loading#lazy) attribute instead of an external library.

There are some things to check:

1. Check all images if they are still shown properly. In 3.0 an element has been added around the `picture` element, this can have an influence on styling
2. The LazyLoad format has to be generated `php artisan media:generate-format --format=lazy-load`
3. To make the transition from placeholder to final image nicer, the following code should be added:

    ```js
    // resources/js/components/_image.js:
    
    // Adds a smooth transition to images that are lazy loaded
    const init = () => {
        const $imageContainers = document.querySelectorAll('[data-image-container-lazyload]')
    
        if (!$imageContainers?.length) return
    
        $imageContainers.forEach(($imageContainer) => {
            const $image = $imageContainer.querySelector('img')
    
            if (!$image) return
    
            if ($image.complete) {
                initSmoothTransitionAfterLazyImageLoad($imageContainer)
            } else {
                $image.addEventListener('load', () => {
                    initSmoothTransitionAfterLazyImageLoad($imageContainer)
                })
            }
        })
    }
    
    const initSmoothTransitionAfterLazyImageLoad = ($imageContainer) => {
        $imageContainer.classList.add('loaded')
    }
    
    init()
    ```

    ```scss
    // resources/sass/general/_image.scss
   
    .image-container--lazyload {
      position: relative;
      z-index: 1;
      overflow: hidden;
      width: fit-content;
    
      .image {
        @include transition;
    
        opacity: 0;
    
        transition-property: opacity;
      }
    
      &.loaded .image {
        opacity: 1;
      }
    
      &::before {
        content: '';
    
        position: absolute;
        z-index: -1;
        inset: 0;
    
        background-image: var(--lazyload-image);
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
      }
    
      &::after {
        content: '';
    
        position: absolute;
        z-index: -1;
        inset: 0;
    
        backdrop-filter: blur(24px);
      }
    }
    ```

### S3 support

This has no breaking change, but can be enabled now, see [here](https://github.com/codedor/filament-media-library/blob/master/docs/index.md#s3-support).

## From v1 to v2

Since [spatie/image](https://spatie.be/docs/image/v3/upgrading) does not rely on Glide anymore, there are some breaking changes for the media formats:

```diff
<?php

namespace App\Formats;

use Codedor\MediaLibrary\Formats\Format;
- use Spatie\Image\Manipulations;
+ use Codedor\MediaLibrary\Formats\Manipulations;
+ use Spatie\Image\Enums\Fit;

class Aspect10x11 extends Format
{
    protected string $description = 'Used in the numbered media list block and in
        the media text block in architect when this format is chosen';

    public function definition(): Manipulations
    {
        return $this->manipulations
-            ->fit(Manipulations::FIT_CROP, 673, 740);
+            ->fit(Fit::Crop, 673, 740);
    }

    public function registerModelsForFormatter(): void
    {
        $this->registerFor(\Codedor\MediaLibrary\Models\Attachment::class);
    }
}
```

Apply this change to all your custom formats. And you should be good to go.
