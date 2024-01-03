<?php

namespace luyadev\mailchimp\tests;

use Yii;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\mailchimp\helpers\MailchimpHelper;
use luya\mailchimp\controllers\DefaultController;

error_reporting(E_ALL);


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
    
    public function testModuleInstanceExceptionsFromController()
    {
        $this->expectException('luya\Exception');
        new DefaultController('default', $this->app->getModule('mailchimp'));
    }
    
    public function testMailchimpHelper()
    {
        $mailchimp = new MailchimpHelper('#unknown', 'de');
        $this->assertFalse($mailchimp->subscribe('wrongListId', 'john@doe.com'));
        $this->assertSame('cURL error 6: Could not resolve host: de.api.mailchimp.com (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://de.api.mailchimp.com/3.0/lists/wrongListId/members', $mailchimp->errorMessage);
    }

    public function testConfigPhp82()
    {

    }
}
