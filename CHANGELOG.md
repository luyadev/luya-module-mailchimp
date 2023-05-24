# LUYA MODULE MAILCHIMP CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 2.1.0 (24. May 2023)

+ Do not make error while saving so verbose.
+ Add $mergeFieldAttributes which should be synced.
+ $options accept a closure function.
+ Added $interests property which also accepts a closure function.

## 2.0.0 (11. May 2023)

+ Updated to mailchimp marketing API version 3.0
+ Remove module `$groups` replaced with `$options`.
+ Removed MailchimpHelper `$updateExisting`, `$replaceInterests` and `$sendWelcome`.
+ MailchimpHelper requres new `$server` construct property.

## 1.0.6 (9. December 2021)

+ Small changes in docs, translations, composer dependencies

## 1.0.5 (6. February 2019)

+ [#12](https://github.com/luyadev/luya-module-mailchimp/issues/12) Use BaseObject instead of Object in order to ensure PHP7 compatibility.

## 1.0.4 (1. June 2018)

+ [#10](https://github.com/luyadev/luya-module-mailchimp/issues/10) Add option for double opt in.

## 1.0.3 (16. April 2018)

+ [#9](https://github.com/luyadev/luya-module-mailchimp/issues/9) Fixed interest groups to correctly work again.

## 1.0.2 (1. November 2017)

+ [#8](https://github.com/luyadev/luya-module-mailchimp/issues/8) Provide an option to disable the robots filtering.

## 1.0.1 (17. October 2017)

+ [#4](https://github.com/luyadev/luya-module-mailchimp/issues/4) **ATTENTION**: Changed the application structure and namespaces of the application, use `luya\mailchimp\Module` instead of `mailchimp\Module`.
+ [#6](https://github.com/luyadev/luya-module-mailchimp/issues/6) Use luya robots filter behavior.
+ [#5](https://github.com/luyadev/luya-module-mailchimp/issues/5) Added Helper method in order to make API calls without instantiating the Mailchimp Module.
+ [#3](https://github.com/luyadev/luya-module-mailchimp/issues/3) Removed error and success variables and moved to standard active form validation with response redirect and flash message.
+ Added LUYA Testsuite in order to make basic application tests.

## 1.0.0 (25. April 2017)

+ First stable release.
