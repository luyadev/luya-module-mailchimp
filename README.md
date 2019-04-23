<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA MAILCHIMP MODULE

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-mailchimp/downloads)](https://packagist.org/packages/luyadev/luya-module-mailchimp)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-module-mailchimp/v/stable)](https://packagist.org/packages/luyadev/luya-module-mailchimp)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

This module provides a simple way to build your own form for a user registration in a MailChimp newsletter.

## Preparation

Before you install and configure the module you've to setup you Mailchimp account. After a successful registration, create a [new mailing list](http://kb.mailchimp.com/lists/growth/create-a-new-list) and [add you list fields](http://kb.mailchimp.com/lists/managing-subscribers/manage-list-and-signup-form-fields). It's recommended to add them directly under *Settings > List fields and *|MERGE|* tags* in your list view. After you've setup your mailing list, extract the [list id](http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id) and note all your list field names. Additionally you'll need to [get your API key for your account](http://kb.mailchimp.com/integrations/api-integrations/about-api-keys). With this informations you're ready to setup the mailchimp luya module.

## Installation

Require the mailchimp module via composer

```sh
composer require luyadev/luya-module-mailchimp
```

add the mailchimp form module to your config:

```php
'modules' => [
    //...
    'newsletter-form' => [
        'class' => 'luya\mailchimp\Module',
        'listId' => 'MailChimp-List-ID',
        'attributes' => [
            'email', 'firstname', 'lastname', 'street', 'zip_city', 'country'
        ],
        'rules' => [
            [['email', 'firstname', 'lastname', 'zip_city', 'country'], 'required'],
            ['email', 'email'],
        ],
        'attributeLabels' => [
            'email' => 'E-Mail',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'street' => 'Street',
            'zip_city' => 'Zip/City',
            'country' => 'Country',
        ],
        'recipients' => [
            'registration-confirm@host.ip',
        ],
        'mailchimpApi' => 'MailChimp API Key',
    ],
```

By default LUYA will wrap the value into the `Yii::t('app', $value)` functions so you are able to translate the attributes labels.
In the above example 'E-Mail' or 'Firstname' would look like this `Yii::t('app', 'E-Mail')` and `Yii::t('app', 'Firstname')`.

Enter your API key in *mailchimpAPI*, your list id in *listid* and all list fields in *attributes*, *rules* and *attributeLabels*. As you can see your list field names in mailchimp will be used as model attributes which then can be validated via [Yii rules](http://www.yiiframework.com/doc-2.0/guide-input-validation.html) defined in *rules*.

## View Files

Create two view files in your module name directory in your views folder: `_mail.php` and `index.php`

### _mail.php

Define your email confirmation admin mail in this view. If you've left `recipient` empty, no confirmation mail will be sent and you can skip the view definition.

```php
<h2><?= Yii::$app->siteTitle;?> Newsletter Registration</h2>
<table border="0" cellpadding="5" cellspacing="2" width="100%">
<?php foreach($model->getAttributes() as $key => $value): ?>
    <tr>
        <td><b><?= $model->getAttributeLabel($key); ?>:</b></td>
        <td><?= nl2br($value); ?></td>
    </tr>
<?php endforeach; ?>
</table>
```

### index.php

Define your registration form in this view. It includes the success and error message output as well.
The form fields have to be correspond to `attributes` definition:

```php
<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

<?php if (Yii::$app->session->getFlash('mailchimpSuccess')): ?>
    <div class="alert alert-success">You've successfully subscribed to the newsletter.</div>
<?php else: ?>
    <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'email'); ?>
        <?= $form->field($model, 'firstname'); ?>
        <?= $form->field($model, 'lastname'); ?>
        <?= $form->field($model, 'zip_city'); ?>
        <?= $form->field($model, 'country'); ?>
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    <?php ActiveForm::end(); ?>
<?php endif; ?>
```

## Send current language

To let Mailchimp know what language the visitor is using, add the field `mc_language` to `attributes` and create a `safe` rule for it (or add it to the `require` rule).

In the view file, add the following line:

```php
<?= $form->field($model, 'mc_language')->hiddenInput(['value'=> Yii::$app->composition->langShortCode])->label(false) ?>
```

## Embed the Module in the CMS

Add a new module page and choose your configured mailchimp-module-name. In the config example above we used *newsletter-form*. Make sure the site is visible and online and you're ready to use the module to register users via your defined custom forms to your mailchimp newsletter list.

### Something isn't working as expected

You can check the API calls to your account in mailchimp account with the given responds on [the same page where you get your API key](http://kb.mailchimp.com/integrations/api-integrations/about-api-keys). Just scroll to the bottom.

