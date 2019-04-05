<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\AddUserForm;

use yii\helpers\VarDumper;
use j7mbo\twitter\TwitterAPIExchange;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
			$settings = array(
				'oauth_access_token' => "1113400947470950401-uqAC4L1sbfNf5sZxSo4Sg1Zd282u4L",
				'oauth_access_token_secret' => "aCcqUOTVc6vpsJ2YeIiE7egkaF5QGU2AeTmS4k4r9hfr0",
				'consumer_key' => "sCxe33EMsMpo0bp3hbTr0k4KL",
				'consumer_secret' => "Ai6z2oY1cPfUf1ZMKGxYDxodkFBjpfodsjR5ZPpn2WAvbCIfG3"
			);

			// Set here the Twitter account from where getting latest tweets
			$people = ['realDonaldTrump', 'BillGates', 'elonmusk'];
			$tweetCount = 5;

			// timeline using TwitterAPIExchange
			$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
			$requestMethod = 'GET';

			$twitter = new TwitterAPIExchange($settings);

			$userTweets = array();

			foreach ($people as $number => $person) {

				$getfield = "?screen_name={$person}&count={$tweetCount}&exclude_replies=true&include_rts=false";

				$userTimeline = $twitter
					->setGetfield($getfield)
					->buildOauth($url, $requestMethod)
					->performRequest();

				$userTimeline = json_decode($userTimeline);

				foreach ($userTimeline as $k => $userTweet) {
			
					$userTweets[$person][$k]['name'] = $userTweet->user->name;
					$userTweets[$person][$k]['profile_image_url'] = $userTweet->user->profile_image_url;
					$userTweets[$person][$k]['text'] = $userTweet->text;
					$userTweets[$person][$k]['created_at'] = $userTweet->created_at;
					if (isset($userTweet->entities->media)) {
						$userTweets[$person][$k]['media_url'] = $userTweet->entities->media[0]->media_url;
					}
	
				}
				
			}

			// echo '<pre>';
			// print_r($userTweets);
			// die();

			$model = new AddUserForm();
			
			if ($model->load(Yii::$app->request->post()) && $model->validate()) {
				// данные в $model удачно проверены

				// делаем что-то полезное с $model ...

				return $this->render('users', ['model' => $model]);
			} else {
				// либо страница отображается первый раз, либо есть ошибка в данных

				// return $this->render('add-user', ['model' => $model]);

				return $this->render('add-user', ['model' => $model, 'userTweets' => $userTweets]);
			}
	 }
	 
	 public function actionAddUser()
    {
        $model = new AddUserForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // данные в $model удачно проверены

            // делаем что-то полезное с $model ...
 
            return $this->render('users', ['model' => $model]);
        } else {
            // либо страница отображается первый раз, либо есть ошибка в данных
            return $this->render('add-user', ['model' => $model]);
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
