# Changelog

All notable changes to `vuetik-laravel` will be documented in this file.

## v2.0.0-beta - 2023-08-06

- Removal of settings image: `persistWidth`, `persistHeight`, and `persistId`.
- image attributes will not removed after transformation
- The twitter container will not removed after transformation
- Vuetik Laravel will now throw when fail to transform by default (for both twitter and image)
- Vuetik Laravel will now auto save by default.
- All the transformed images url will now be transformed to Glide url if the config is enabled
- Default class `vuetik__failed__img` for failing pre-upload image only if throw on fail setting is disabled

## v1.0.1 - 2023-08-02

- Image Manager `store()` will now return array of VuetikImages saved models.
- New option `image.persistId` to keep the `data-image-id` attribute still rendered in HTML.

## v1.0-beta - 2023-07-20

A beta release of Vuetik Laravel.

## V1.0 Beta - 2023-07-20

This release introduces Vuetik Laravel as a ready to use library.
