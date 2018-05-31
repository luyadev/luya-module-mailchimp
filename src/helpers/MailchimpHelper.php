<?php

namespace luya\mailchimp\helpers;

use Mailchimp;
use yii\base\Object;

/**
 * Mailchimp Helper.
 *
 * Usage of Mailchimp subscriptions without controllers, example:
 *
 * ```php
 * $mailchimp = new MailchimpHelper('API_KEY');
 * if ($mailchimp->subsribe('LIST_ID', 'john@doe.com')) {
 *     echo  "Subscription complet!";
 * } else {
 *     echo "Error:" . $mailchimp->errorMessage;
 * }
 * ```
 *
 * @property string $errorMessage Contains an error messsage when an api call was false.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.1
 */
class MailchimpHelper extends Object
{
    /**
     * @var array List of arrays an the translations (https://apidocs.mailchimp.com/api/1.3/exceptions.field.php)
     */
    protected $errors = [
        214 => 'E-Mail is already subscribed to this list.',
    ];
    
    /**
     * @var Mailchimp;
     */
    public $mailchimp;
    
    /**
     * @var boolean
     * @since 1.0.4
     */
    public $doubleOptin = false;
    
    /**
     * @var boolean
     * @since 1.0.4
     */
    public $updateExisting = false;
    
    /**
     * @var boolean
     * @since 1.0.4
     */
    public $replaceInterests = false;
    
    /**
     * @var boolean
     * @since 1.0.4
     */
    public $sendWelcome = false;
    
    /**
     *
     * @param string $apiKey
     * @param array $config
     */
    public function __construct($apiKey, array $config = [])
    {
        $this->mailchimp = new \Mailchimp($apiKey);
        
        parent::__construct($config);
    }
    
    /**
     * Subscribe an email adresse to a given list.
     *
     * @param string $listId
     * @param string $email
     * @param array $mergedVars
     * @param array $options
     * @return boolean|mixed
     */
    public function subscribe($listId, $email, array $mergedVars = [], array $options = [])
    {
        try {
            // description of subscribe properties:
            // subscribe($id, $email, $merge_vars=null, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false)
            
            return $this->mailchimp->lists->subscribe($listId, [
                'email' => $email,
            ], $mergedVars, 'html', $this->doubleOptin, $this->updateExisting, $this->replaceInterests, $this->sendWelcome);
            
        } catch (\Exception $e) {
            return $this->setErrorMessage($e);
        }
    }

    private $_errorMessage;
    
    /**
     * Error Message Setter Method.
     *
     * If the given code is found inside the exception the message from the prepared error list is used instead of the exception message.
     *
     * @param \Exception $error
     * @return boolean
     */
    public function setErrorMessage(\Exception $error)
    {
        $this->_errorMessage = isset($this->errors[$error->getCode()]) ? $this->errors[$error->getCode()] : $error->getMessage();
        
        return false;
    }
    
    /**
     * Error Message Getter Method.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
}
