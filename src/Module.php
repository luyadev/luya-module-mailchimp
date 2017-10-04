<?php

namespace luya\mailchimp;

use luya\Exception;
use Yii;

/**
 * LUYA MAILCHIMP FORM SUBSCRIBE
 *
 * example configuration:
 *
 * ```
 * 'newsletter-audience-form' => [
 *  'class' => 'mailchimp\Module',
 *  'listId' => 'MailChimp-ListID',
 *  'attributes' => [
 *   'email', 'CUSTOMVAR1', 'CUSTOMVAR2', 'CUSTOMVAR3', 'CUSTOMVAR4'
 *  ],
 *  'rules' => [
 *   [['email', 'CUSTOMVAR1', 'CUSTOMVAR2', 'CUSTOMVAR3', 'CUSTOMVAR4'], 'required'],
 *   ['email', 'email'],
 *  ],
 *  'attributeLabels' => [
 *   'email' => 'E-Mail',
 *   'CUSTOMVAR1' => 'Vorname',
 *   'CUSTOMVAR2' => 'Nachname',
 *   'CUSTOMVAR3' => 'Strasse',
 *   'CUSTOMVAR4' => 'PLZ/Ort',
 *  ],
 * 'recipients' => [
 *  'successmail@host.ip',
 * ],
 * 'mailchimpApi' => 'APIKEY',
 * ],
 * ```
 *
 * @author martinpetrasch
 * @since 1.0.0-beta6
 */
class Module extends \luya\base\Module
{
    /**
     * @var boolean By default this module will lookup the view files in the appliation view folder instead of
     * the module base path views folder.
     */
    public $useAppViewPath = true;

    /**
     * @var callable You can define a anonmys function which will be trigger on success, the first parameter of the
     * function can be the model which will be assigned [[\luya\base\DynamicModel]]. Example callback
     *
     * ```php
     * $callback = function($model) {
     *     // insert the name of each contact form into `contact_form_requests` table:
     *     Yii::$db->createCommand()->insert('contact_form_requests', ['name' => $model->name])->execute();
     * }
     * ```
     */
    public $callback;

    /**
     *
     * @var array An array with all recipients the mail should be sent on success, recipients will be assigned via
     * [[\luya\components\Mail::addresses()]] method of the mailer function.
     */
    public $recipients;

    /**
     * @var int Number in seconds, if the process time is faster then `$spamDetectionDelay`, the mail will threated as spam
     * and throws an exception. As humans requires at least more then 2 seconds to fillup a form we use this as base value.
     */
    public $spamDetectionDelay = 2;

    /**
     * @var string MailChimp API key. You'll find more info how to make or retrieve an API key here: http://kb.mailchimp.com/accounts/management/about-api-keys
     */
    public $mailchimpApi;

    /**
     * @var Number MailChimp newsletter list Id.
     */
    public $listId;

    /**
     * @var array An array containing all the attributes for this model
     *
     * ```php
     * 'attributes' => ['name', 'email', 'street', 'city', 'tel', 'message'],
     * ```
     */
    public $attributes;

    /**
     * @var string Defines the name of the attribute in the model which containts the email adresse in order to make the mailchimp call and also to assign the errors.
     */
    public $attributeEmailField = 'email';

    /**
     * @var array An array define the rules for the corresponding attributes. Example rules:
     *
     * ```php
     * rules' => [
     *     [['name', 'email', 'street', 'city', 'message'], 'required'],
     *     ['email', 'email'],
     * ],
     * ```
     */
    public $rules = [];

    /**
     * @var array An array define the attribute labels for an attribute, internal the attribute label values
     * will be wrapped into the `Yii::t()` method.
     *
     * ```php
     * 'attributeLabels' => [
     *     'email' => 'E-Mail-Adresse',
     * ],
     * ```
     */
    public $attributeLabels = [];

    /**
     * @var array Group fields defined in your mailchimp list. Contains an array with an alias for model validation, the id for mailchimp API submit. The included fields will be defined in your form view.
     *
     * ```php
     * 'groups' => [
     *  [
     *   'alias' => 'language',
     *   'id' => '2809',
     *  ],
     * ],
     * ```
     */
    public $groups = [];
}
