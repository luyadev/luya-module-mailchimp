LUYA MAILCHIMP REGISTRATION FORM MODULE
=======================================

This module provides a simple way to build your own form for a user registration in a MailChimp newsletter.

Installation
----

Require the mailchimp module via composer

```sh
composer require luyadev/luya-module-mailchimp
```

add the mailchimp form module to your config:

```php
'modules' => [
    //...
    'newsletter-form' => [
        'class' => 'mailchimp\Module',
        'listId' => 'MailChimp-List-ID',
        'attributes' => [
            'email', 'FIRSTNAME', 'LASTNAME', 'STREET', 'ZIP_CITY', 'COUNTRY'
        ],
        'rules' => [
            [['email', 'FIRSTNAME', 'LASTNAME', 'ZIP_CITY', 'COUNTRY'], 'required'],
            ['email', 'email'],
        ],
        'attributeLabels' => [
            'email' => 'E-Mail',
            'FIRSTNAME' => 'Firstname',
            'LASTNAME' => 'Lastname',
            'STREET' => 'Street',
            'ZIP_CITY' => 'Zip/City',
            'COUNTRY' => 'Country',
        ],
        'recipients' => [
            'registration-confirm@host.ip',
        ],
        'mailchimpApi' => 'MailChimp API Key',
    ],
```

By default LUYA will wrap the value into the `Yii::t('app', $value)` functions so you are able to translate the attributes labels.
In the above example 'E-Mail' or 'Firstname' would look like this `Yii::t('app', 'E-Mail')` and `Yii::t('app', 'Firstname')`.

#### View Files

Create two view files in your module name directory in your views folder: ```_mail.php``` and ``ìndex.php```

##_mail.php##

Define your email confirmation admin mail in this view. If you've left `recipient` empty, no confirmation mail will be sent and you can skip the view definition.

```
<?php?>
<h2><?= Yii::$app->siteTitle;?> Newsletter registration</h2>
<p>Date: <?= date("d.m.Y H:i"); ?></p>
<table border="0" cellpadding="5" cellspacing="2" width="100%">
    <?php foreach($model->getAttributes() as $key => $value): ?>
    <tr><td width="150" style="border-bottom:1px solid #F0F0F0"><?= $model->getAttributeLabel($key); ?>:</td><td style="border-bottom:1px solid #F0F0F0"><?= nl2br($value); ?></td>
        <?php endforeach; ?>
</table>
```

##index.php##

Define your registration form in this view. It includes the success and error message output as well.
The form fields have to be correspond to `attributes` definition:

```php
<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var object $model Contains the model object based on DynamicModel yii class.
 * @var boolean $success Return true after successful newsletter registration and confirmation mail sent (if applicable)
 */
?>

<? if (!empty($error)): ?>
    <? $errorMessage = $error->getMessage() . ' (' . $error->getCode() . ')';

    if ($error->getCode() == \mailchimp\Module::ERROR_EMAIL_ALREADY_SUBSCRIBED) {
        $errorMessage = Yii::t('app', 'E-Mail is already subscribed to the list.');
    }
    ?>
    <? if (isset($errorMessage) && (!empty($errorMessage))): ?>
        <div class="alert alert-danger">
            <h3>An error occured.</h3>
            <?= $errorMessage; ?>
        </div>
    <? endif ?>
<? endif ?>

<? if ($success): ?>
    <div class="alert alert-success">You've successfully subscribed to the newsletter.</div>
<? else: ?>
    <? $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'FIRSTNAME'); ?>
    <?= $form->field($model, 'LASTNAME'); ?>
    <?= $form->field($model, 'ZIP_CITY'); ?>
    <?= $form->field($model, 'COUNTRY'); ?>

    <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>

    <? ActiveForm::end(); ?>
<? endif; ?>
```
