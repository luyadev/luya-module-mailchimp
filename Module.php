<?php

namespace mailchimp;

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
class Module extends \admin\base\Module
{
    /**
     * error code list: https://apidocs.mailchimp.com/api/1.3/exceptions.field.php
     */
    const ERROR_EMAIL_ALREADY_SUBSCRIBED = 214;

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
    public $callback = null;

    /**
     *
     * @var array An array with all recipients the mail should be sent on success, recipients will be assigned via
     * [[\luya\components\Mail::adresses()|adresses()]] method of the mailer function.
     */
    public $recipients = null;

    /**
     * @var int Number in seconds, if the process time is faster then `$spamDetectionDelay`, the mail will threated as spam
     * and throws an exception. As humans requires at least more then 2 seconds to fillup a form we use this as base value.
     */
    public $spamDetectionDelay = 0;

    /**
     * @var string MailChimp API key. You'll find more info how to make or retrieve an API key here: http://kb.mailchimp.com/accounts/management/about-api-keys
     */
    public $mailchimpApi = null;

    /**
     * @var Number MailChimp newsletter list Id.
     */
    public $listId;

    /**
     * @var array An array containing all the attributes for this model
     *
     * ```
     * 'attributes' => ['name', 'email', 'street', 'city', 'tel', 'message'],
     * ```
     */
    public $attributes = null;

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
     * ```
     * 'attributeLabels' => [
     *     'email' => 'E-Mail-Adresse',
     * ],
     * ```
     */
    public $attributeLabels = [];

    /**
     * {@inheritDoc}
     * @see \luya\base\Module::init()
     */
    public function init()
    {
        parent::init();

        if ($this->listId === null) {
            throw new Exception("The MailChimp list Id must be defined.");
        }
        if ($this->recipients === null) {
            throw new Exception("The recipients attributed must be defined with an array of recipients who will recieve an email.");
        }

        if ($this->mailchimpApi === null) {
            throw new Exception("The MailChimp API key must be defined.");
        }

        if ($this->attributes === null) {
            throw new Exception("The attributes attributed must be defined with an array of available attributes.");
        }
    }

    public $translations = [
        [
            'prefix' => 'mailchimp*',
            'basePath' => '@form1/messages',
            'fileMap' => [
                'mailchimp' => 'mailchimp.php',
            ],
        ],
    ];

    public static function t($message, array $params = [])
    {
        return Yii::t('mailchimp', $message, $params, Yii::$app->luyaLanguage);
    }

}