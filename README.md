# Media Library For Filament

Introducing "Media Library", a comprehensive package that simplifies the management of file uploads and media
files in your Laravel and Filament application.

This package provides a fluent and intuitive API that allows you to seamlessly integrate file uploads into your Laravel
application. You can easily define the accepted file formats, set upload limits, and specify storage locations for your
media files. The package also includes a simple and elegant user interface for managing your media library, making it
easy to organize, search, and retrieve your files.

With Media Library, you can upload and manage any type of media file, including images, videos, audio files, and
documents. You can also add metadata to your files, such as titles, descriptions, and tags, making it easy to search and
filter your media library.

The package provides a robust set of features, including automatic file resizing, image manipulation, and thumbnail
generation. You can easily create custom transformations for your images, and even define presets that can be applied to
multiple images at once.

Media Library is also highly customizable, with a flexible configuration system that allows you to tailor the
package to your specific needs. Whether you're building a simple blog or a complex web application, Laravel Media
Library provides a simple and elegant solution for managing your media files.

![Overview page](docs/index.png)

![Formatter modal](docs/formatter.png)

## Installation

You can install the package via composer:

```bash
composer require wotz/filament-media-library
```

Run the migrations with:

```bash
php artisan migrate
```

## Documentation

For the full documentation, check [here](./docs/index.md).

## Testing

```bash
vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Upgrading

Please see [UPGRADING](UPGRADING.md) for more information on how to upgrade to a new version.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email info@whoownsthezebra.be instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
