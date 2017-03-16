# Yii2 API Skeleton Project

[![Packagist Pre Release](https://img.shields.io/packagist/vpre/charlesportwoodii/yii2-api.svg?maxAge=86400?style=flat-square)](https://packagist.org/packages/charlesportwoodii/yii2-api)
[![TravisCI](https://img.shields.io/travis/charlesportwoodii/yii2-api.svg?style=flat-square "TravisCI")](https://travis-ci.org/charlesportwoodii/yii2-api)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/charlesportwoodii/yii2-api.svg?style=flat-square)](https://scrutinizer-ci.com/g/charlesportwoodii/yii2-api/)
[![Gittip](https://img.shields.io/gittip/charlesportwoodii.svg?style=flat-square "Gittip")](https://www.gittip.com/charlesportwoodii/)
[![License](https://img.shields.io/badge/license-BSD-orange.svg?style=flat-square "License")](https://github.com/charlesportwoodii/yii2-api/blob/master/LICENSE.md)
[![Yii](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat-square)](http://www.yiiframework.com/)

A project template to reduce the overhead involved in writing RESTful JSON API's by implementing common API endpoints (such as registration and authentication) so that developers can focus on writing core application business logic rather than implementing the same components over and over again. Built ontop of Yii Framework 2 (Yii2), this project provides a _basline_ API skeleton that is easy to extend from, and implements a base API that can easily be extended from.

## What is Provided?

By default the following functionality is provided:

- Authentication with HMAC+HKDF
- Registration
- Password Resets for authenticate and unauthenticated users

The additional functionality is provided as well:

- Two factor authentication via OTP codes + API endpoints to manage
- Configurable Rate Limiting
- Encrypted API session support via libsodium
- Translation support

## Documentation

For information on how to setup, configure, extend, and use this framework, please read the documentation in the [docs](docs) folder.

## How to Contribute

You can contribute to the development of the core API by submitting a new issue or pull request to this repository, or to the [yii2-api-rest-components](https://github.com/charlesportwoodii/yii2-api-rest-components), where the core components are stored. If you're looking for something to contribute to, consider the following ideas:

- Translations
- Implementing new API endpoints common to API's

## License

See [LICENSE.md](LICENSE.md) for licensing information.
