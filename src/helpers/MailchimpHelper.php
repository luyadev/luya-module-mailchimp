<?php

namespace luya\mailchimp\helpers;

use Mailchimp;
use yii\base\Object;

/**
 * Helper Methods.
 *
 * @property string $errorMessage  Contains an error messsage when an api call was false.
 * 
 * @author Basil Suter <basil@nadar.io>
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
            return $this->mailchimp->lists->subscribe($listId, ['email' => $email], $mergedVars, false, false, false, false);
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
