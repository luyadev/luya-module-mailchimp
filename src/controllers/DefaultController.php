<?php

namespace luya\mailchimp\controllers;

use Yii;
use luya\base\DynamicModel;
use yii\base\InvalidConfigException;
use luya\Exception;
use luya\web\Controller;
use \Mailchimp;
use luya\mailchimp\helpers\MailchimpHelper;

class DefaultController extends Controller
{
    /**
     * Initializer
     * @todo use getter exception inside module attribute instead of initializer of controller.
     */
    public function init()
    {
        parent::init();
        
        if ($this->module->listId === null) {
            throw new Exception("The MailChimp list Id must be defined.");
        }
        
        if ($this->module->mailchimpApi === null) {
            throw new Exception("The MailChimp API key must be defined.");
        }
        
        if ($this->module->attributes === null) {
            throw new Exception("The attributes attributed must be defined with an array of available attributes.");
        }
    }
    
    /**
     * @param $model
     * @param $alias name of the mailchimp group in our dynamic model
     * @return array|null return the included elements as a list
     */
    private function getGroupAttributes($model, $alias)
    {
        $returnArray = null;
        if (isset($model->{$alias}) && is_array($model->{$alias})) {
            foreach ($model->{$alias} as $element) {
                $returnArray[] = $element;
            }
        }
        
        return $returnArray;
    }

    /**
     * 
     * @throws InvalidConfigException
     * @return \luya\base\DynamicModel
     */
    private function generateModelFromModule()
    {
        $model = new DynamicModel($this->module->attributes);
        $model->attributeLabels = $this->module->attributeLabels;
        
        foreach ($this->module->rules as $rule) {
            if (is_array($rule) && isset($rule[0], $rule[1])) {
                $model->addRule($rule[0], $rule[1], isset($rule[2]) ? $rule[2] : []);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
        
        return $model;
    }
    
    /**
     * Index Action
     *
     * @throws InvalidConfigException
     * @return string
     */
    public function actionIndex()
    {
        $model = $this->generateModelFromModule();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ((intval(time()) - intval(Yii::$app->session->get('renderTime', 0))) < $this->module->spamDetectionDelay) {
                throw new Exception("We haved catched a spam contact form with the values: " . print_r($model->attributes, true));
            }

            $merge_vars = null;
            foreach ($model->attributes as $key => $value) {
                if ($key != $this->module->attributeEmailField) {
                    $merge_vars[$key] = $value;
                }
            }

            // add interest groups
            foreach ($this->module->groups as $group) {
                $merge_vars['groupings'][] = [
                    [
                        'id' => $group['id'],
                        'groups' => $this->getGroupAttributes($model, $group['alias'])
                    ]
                ];
            }

            try {
                (new MailchimpHelper($this->module->mailchimpApi))->subscribe($this->module->listId, $model->{$this->module->attributeEmailField}, $merge_vars);
            } catch (\Mailchimp_Error $e) {
                $model->addError($this->module->attributeEmailField, $e->getMessage());
            }
            
            if (!$model->hasErrors()) {
                if ($this->module->recipients) {
                    $mail = Yii::$app->mail->compose('['.Yii::$app->siteTitle.'] newsletter registration', $this->renderPartial('_mail', ['model' => $model]));
                    $mail->adresses($this->module->recipients);
                    if ($mail->send()) {
                        // callback
                        $cb = $this->module->callback;
                        if (is_callable($cb)) {
                            $cb($model);
                        }
                    }
                }
                
                Yii::$app->session->setFlash('mailchimpSuccess');
                
                return $this->refresh();
            }
        }

        Yii::$app->session->set('renderTime', time());
        
        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
