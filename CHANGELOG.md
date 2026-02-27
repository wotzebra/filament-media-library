# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### What's Changed

* E20-137 Add attachment versioning: replace files and revert to previous versions

---

## v4.0.1 - 2026-02-18

### What's Changed

* Formatter updates so we can save in higher quality

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v4.0.0...v4.0.1

## v4.0.0 - 2026-01-20

### What's Changed

* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/codedor/filament-media-library/pull/71
* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/codedor/filament-media-library/pull/70
* Upgrade to Filament v4 by @jyrkidn in https://github.com/codedor/filament-media-library/pull/57
* Upgrade to Filament v4 by @jyrkidn in https://github.com/codedor/filament-media-library/pull/69

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v3.1.0...v4.0.0

## v3.1.0 - 2025-11-20

### What's Changed

* Add translatable strings by @jyrkidn in https://github.com/codedor/filament-media-library/pull/55
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/codedor/filament-media-library/pull/61
* Bump stefanzweifel/git-auto-commit-action from 5 to 7 by @dependabot[bot] in https://github.com/codedor/filament-media-library/pull/65

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v3.0.4...v3.1.0

## v3.0.4 - 2025-09-11

### What's Changed

* Fixed `GenerateFormats` command with `--force` flag overwriting manually cropped images. The command now applies existing crop coordinates from the `attachment_formats` table when regenerating formats, preserving user's manual cropping adjustments.
* Add timestamp to format url if the format has been cropped, otherwise the user does not see the changes due to cache

## v3.0.3 - 2025-08-08

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/codedor/filament-media-library/pull/56
* Added support for temporary uploads stored on a s3 disk by @jyrkidn in https://github.com/codedor/filament-media-library/pull/54
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/codedor/filament-media-library/pull/59
* INF001-110 Add JPG and PNG (in capital letters) to the allowed image extensions by @jyrkidn in https://github.com/codedor/filament-media-library/pull/60

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v3.0.2...v3.0.3

## v3.0.2 - 2025-04-17

### What's Changed

* Wrap width and height attributes in double quotes

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v3.0.1...v3.0.2

## v3.0.1 - 2025-03-24

### What's Changed

* Fixed broken search

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v3.0.0...v3.0.1

## v3.0.0 - 2025-02-28

### What's Changed

* 3.x by @jyrkidn in https://github.com/codedor/filament-media-library/pull/49

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v2.1.0...v3.0.0

## v2.1.0 - 2025-02-28

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/codedor/filament-media-library/pull/50
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/codedor/filament-media-library/pull/52
* Upgrade to L12 by @jyrkidn in https://github.com/codedor/filament-media-library/pull/53
* FIL001-162 Hide certain tags in the overview & resource picker by @jyrkidn in https://github.com/codedor/filament-media-library/pull/51

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v2.0.3...v2.1.0

## v2.0.3 - 2024-12-04

### What's Changed

* FIL001-147 add tags filter by @thibautdeg in https://github.com/codedor/filament-media-library/pull/45

### New Contributors

* @thibautdeg made their first contribution in https://github.com/codedor/filament-media-library/pull/45

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v2.0.2...v2.0.3

## v2.0.2 - 2024-12-03

### What's Changed

* Fix multiple breaking when deleting first image in list by @AngryMoustache in https://github.com/codedor/filament-media-library/pull/46

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v2.0.1...v2.0.2

## v1.2.1 - 2024-12-03

### What's Changed

* [1.x] Apply fix for multiple attachment input when deleting items by @jyrkidn in https://github.com/codedor/filament-media-library/pull/47

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.2.0...v1.2.1

## v2.0.1 - 2024-11-12

### What's Changed

* Fix uppercase extensions not saving properly by @jyrkidn in https://github.com/codedor/filament-media-library/pull/40

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v2.0.0...v2.0.1

## v2.0.0 - 2024-10-04

### What's Changed

* Add Laravel 11 support by @gdebrauwer in https://github.com/codedor/filament-media-library/pull/39

### New Contributors

* @gdebrauwer made their first contribution in https://github.com/codedor/filament-media-library/pull/39

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.2.0...v2.0.0

## v1.2.0 - 2024-08-30

### What's Changed

* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot in https://github.com/codedor/filament-media-library/pull/38
* Upgrade to translatable tabs v1.2.0 by @jyrkidn in https://github.com/codedor/filament-media-library/pull/41

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.1.1...v1.2.0

## v1.1.1 - 2024-07-04

### What's Changed

* Added a config to allow the user to run attachment format jobs on a seperate queue

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.1.0...v1.1.1

## v1.1.0 - 2024-04-16

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.3.1 to 2.4 by @dependabot in https://github.com/codedor/filament-media-library/pull/33
* update background & text color placeholder by @jyrkidn in https://github.com/codedor/filament-media-library/pull/34

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.0.14...v1.1.0

## v1.0.14 - 2024-03-28

### What's Changed

* Fix title issues, refs #FIL001-109 by @Thomva in https://github.com/codedor/filament-media-library/pull/28
* Bump dependabot/fetch-metadata from 1.6.0 to 2.0.0 by @dependabot in https://github.com/codedor/filament-media-library/pull/27
* Bump ramsey/composer-install from 1 to 3 by @dependabot in https://github.com/codedor/filament-media-library/pull/25
* Small layout upgrades by @Katrienvh in https://github.com/codedor/filament-media-library/pull/26

### New Contributors

* @Katrienvh made their first contribution in https://github.com/codedor/filament-media-library/pull/26

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.0.13...v1.0.14

## v1.0.13 - 2024-02-09

### What's Changed

* Feature/fil001 106 fix lazyload by @Thomva in https://github.com/codedor/filament-media-library/pull/23
* add title on image in media library by @DevolderLouise in https://github.com/codedor/filament-media-library/pull/22

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.0.12...v1.0.13

## v1.0.12 - 2024-01-29

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.0.11...v1.0.12

## v1.0.10 - 2024-01-26

**Full Changelog**: https://github.com/codedor/filament-media-library/compare/v1.0.9...v1.0.10

## v1.0.7 - 2023-12-26

### What's Changed?

- Stying for the buttons on the media picker

## v1.0.4 - 2023-12-13

### Fixed

- Also generate webp formats when formatting using the tool

## v0.3.6 - 2023-11-02

### What's changed?

Updated resource picker to new version

## v0.3.5 - 2023-10-25

#What's changed?

- Search on name + extension
- Added disk filter
- Removed private files from resource picker

## v0.3.4 - 2023-09-29

### What's changed?

#### Fixed

- Rewrote the way we fetch allowed formats, this gave issue sin the settings module

## v0.3.3 - 2023-09-28

### What's changed

#### Fixed

- Resource picker not picking attachments correctly in nested modals

## v0.3.2 - 2023-09-21

### What's changed

#### Fixed

- Removed empty space caused by the modals at the bottom of all Filament pages

## v0.3.1 - 2023-09-21

### What's changed

#### Fixed

- Don't show formatter when there are no formats to format in the formatter

## v0.3.0 - 2023-09-21

### What's changed

#### Added

- Added resource picker
- Reworked the way formats are registered

## v0.2.0 - 2023-08-30

### What's Changed

- Removed the Models facade in favor of the Formats facade

**Full Changelog**: https://github.com/codedor/filament-media-library/commits/v0.2.0

## [Unreleased]
