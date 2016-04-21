<?php
namespace mailchimp\controllers;

use Yii;
use luya\base\DynamicModel;
use yii\base\InvalidConfigException;
use luya\Exception;
use \Mailchimp;

class DefaultController extends \luya\web\Controller
{
    /**
     * @var null|bool if null no status information has been assigned, if false a global error happend (could not send mail), if true
     * the form has been sent successfull.
     */
    public $success = null;

    const ERROR_EMAIL_ALREADY_SUBSCRIBED = 214;

    /**
     * Index Action
     *
     * @throws InvalidConfigException
     * @return string
     */
    public function actionIndex()
    {
        // create dynamic model
        $model = new DynamicModel($this->module->attributes);
        $model->attributeLabels = $this->module->attributeLabels;
        $error = null;

        foreach ($this->module->rules as $rule) {
            if (is_array($rule) && isset($rule[0], $rule[1])) {
                $model->addRule($rule[0], $rule[1], isset($rule[2]) ? $rule[2] : []);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ((intval(time()) - intval(Yii::$app->session->get('renderTime', 0))) < $this->module->spamDetectionDelay) {
                throw new Exception("We haved catched a spam contact form with the values: " . print_r($model->attributes, true));
            }

            $mailchimp = new Mailchimp($this->module->mailchimpApi);

            $merge_vars = null;
            foreach ($model->attributes as $key=>$value) {
                if ($key != 'email') {
                    $merge_vars[$key] = $value;
                }
            }

            try {
                $result = $mailchimp->lists->subscribe($this->module->listId, array('email' => $model->email),
                    $merge_vars,
                    false, false, false, false);
            } catch(\Mailchimp_Error $e) {
                $error = $e;
            }

            if (empty($error)) {
                // send admin success mail
                if ($this->module->recipients !== null) {
                    $mail = Yii::$app->mail->compose('['.Yii::$app->siteTitle.'] newsletter registration', $this->renderPartial('_mail', ['model' => $model]));
                    $mail->adresses($this->module->recipients);

                    if ($mail->send()) {
                        $this->success = true;

                        // callback
                        $cb = $this->module->callback;
                        if (is_callable($cb)) {
                            $cb($model);
                        }

                    } else {
                        $this->success = false;
                    }
                } else {
                    $this->success = true;
                }
            }
        }

        Yii::$app->session->set('renderTime', time());

        return $this->render('index', [
            'model' => $model,
            "error" => $error,
            'success' => $this->success,
        ]);
    }
}