<?php

namespace luyadev\mailchimp\tests;

use luya\testsuite\cases\WebApplicationTestCase;
use luya\mailchimp\helpers\MailchimpHelper;

class MailchimpTest extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'mailchimp',
            'basePath' => dirname(__DIR__),
            'modules' => [
                'mailchimp' => [
                    'class' => 'luya\mailchimp\Module',
                ],
            ],
        ];
    }
    
    public function testGetModuleInstance()
    {
        $this->assertInstanceOf('luya\mailchimp\Module', $this->app->getModule('mailchimp'));
    }
    
    public function testMailchimpHelper()
    {
        $mailchimp = new MailchimpHelper('#unknown');
        $this->assertFalse($mailchimp->subscribe('wrongListId', 'john@doe.com'));
        $this->assertSame('Invalid MailChimp API key: #unknown', $mailchimp->errorMessage);
    }
}
