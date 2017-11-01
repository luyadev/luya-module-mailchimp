<?php

namespace luyadev\mailchimp\tests;

use Yii;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\mailchimp\helpers\MailchimpHelper;
use luya\mailchimp\controllers\DefaultController;

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
            'params' => include('_apikeys.php'),
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
        $mailchimp = new MailchimpHelper('#unknown');
        $this->assertFalse($mailchimp->subscribe('wrongListId', 'john@doe.com'));
        $this->assertSame('Invalid MailChimp API key: #unknown', $mailchimp->errorMessage);
    }
    
    public function testMailchimpHelperSuccess()
    {
        $mailchimp = new MailchimpHelper(Yii::$app->params['apiKey']);
        $response = $mailchimp->subscribe(Yii::$app->params['listId'], 'basil+'.time().'@nadar.io');
        $this->assertNotFalse($response);
    }

    public function testRobotsSpamDelay()
    {
        $module = $this->app->getModule('mailchimp');
        $module->listId = '123';
        $module->mailchimpApi = '123';
        $module->attributes = ['foo' => 'bar'];
        
        $_SERVER['REQUEST_METHOD'] = 'post';
        $this->expectException('yii\base\InvalidCallException');
        (new DefaultController('default', $module))->runAction('index');
    }
    
    public function testRobotsSpamDelayDisabled()
    {
        $module = $this->app->getModule('mailchimp');
        $module->robotsFilterDelay = false;
        $module->listId = '123';
        $module->mailchimpApi = '123';
        $module->attributes = ['foo' => 'bar'];
        
        $ctrl = (new DefaultController('default', $module));
        $behaviors = $ctrl->behaviors();
        
        $this->assertEmpty($behaviors);
    }
}
