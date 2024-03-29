<?php

namespace luya\mailchimp\helpers;

use MailchimpMarketing\Api\ListsApi;
use MailchimpMarketing\ApiClient;
use Yii;
use yii\base\BaseObject;

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
class MailchimpHelper extends BaseObject
{
    /**
     * @var array List of arrays an the translations (https://apidocs.mailchimp.com/api/1.3/exceptions.field.php)
     */
    protected $errors = [
        214 => 'E-Mail is already subscribed to this list.',
    ];
    
    /**
     * @var ApiClient;
     */
    public $mailchimp;
    
    /**
     * @var boolean
     * @since 1.0.4
     */
    public $doubleOptin = false;
    
    /**
     *
     * @param string $apiKey
     * @param array $config
     */
    public function __construct($apiKey, $server, array $config = [])
    {
        $this->mailchimp = new ApiClient();
        $this->mailchimp->setConfig([
            'apiKey' => $apiKey,
            'server' => $server, // us19 f.e
        ]);
        
        parent::__construct($config);
    }
    
    /**
     * Subscribe an email adresse to a given list.
     *
     * @param string $listId
     * @param string $email
     * @param array $options An option would be:
     * - interests:
     * - language
     * @param array $mergeFields
     * @param array $interests ['123123' => true], wheere 123123 is the interest/group attribute id. In order to find the id use the api: https://<SERVER>.api.mailchimp.com/3.0/lists/<LISTID>/interest-categories
     * @return boolean|mixed
     * @see https://mailchimp.com/developer/marketing/api/list-members/add-member-to-list/
     */
    public function subscribe($listId, $email, array $options = [], array $mergeFields = [], array $interests = [])
    {
        try {
            // https://mailchimp.com/developer/marketing/api/list-members/add-member-to-list/
            /** @var ListsApi $lists */
            $lists = $this->mailchimp->lists;
            return $lists->addListMember($listId, array_filter(array_merge([
                'email_address' => $email,
                'status' => $this->doubleOptin ? 'pending' : 'subscribed', // "subscribed", "unsubscribed", "cleaned", "pending", or "transactional".
                'email_type' => 'html',
                'merge_fields' => $mergeFields,
                'interests' => $interests,
            ], $options)));

        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
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
