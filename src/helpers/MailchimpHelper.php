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
     * @param \Exception $error
     * @return boolean
     */
    public function setErrorMessage(\Exception $error)
    {
        $this->_errorMessage = $error->getMessage();
        
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
