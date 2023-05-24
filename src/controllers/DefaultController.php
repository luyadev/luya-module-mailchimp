<?php

namespace luya\mailchimp\controllers;

use Yii;
use luya\base\DynamicModel;
use yii\base\InvalidConfigException;
use luya\Exception;
use luya\web\Controller;
use luya\mailchimp\helpers\MailchimpHelper;
use luya\mailchimp\Module;
use luya\web\filters\RobotsFilter;

/**
 * Default Controller.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 * @property Module $module
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        if ($this->module->robotsFilterDelay !== false) {
            $behaviors['robotsFilter'] = [
                'class' => RobotsFilter::class,
                'delay' => $this->module->robotsFilterDelay,
            ];
        }
        
        return $behaviors;
    }
    
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

        $labels = [];
        foreach ($this->module->attributeLabels as $key => $label) {
            $labels[$key] = is_array($label) ? Yii::t($label[0], $label[1]) : $label;
        }
        $model->attributeLabels = $labels;
        
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
            $merge_vars = null;
            foreach ($model->attributes as $key => $value) {
                if ($key != $this->module->attributeEmailField) {
                    $merge_vars[$key] = $value;
                }
            }

            $mailchimp = new MailchimpHelper($this->module->mailchimpApi, $this->module->server);
            $mailchimp->doubleOptin = $this->module->doubleOptin;
            
            if (!$mailchimp->subscribe($this->module->listId, $model->{$this->module->attributeEmailField}, $this->module->options, $merge_vars)) {
                $model->addError($this->module->attributeEmailField, $mailchimp->errorMessage);
            }
            
            if (!$model->hasErrors()) {
                if ($this->module->recipients) {
                    $mail = Yii::$app->mail->compose('['.Yii::$app->siteTitle.'] newsletter registration', $this->renderPartial('_mail', ['model' => $model]));
                    $mail->addresses($this->module->recipients);
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
        
        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
