<?php

namespace app\controllers;

use yii\rest\ActiveController;
use j7mbo\twitter\TwitterAPIExchange;

use app\models\User;

// URLs
//curl -i -H "Accept:application/json" "http://yii-twitter/users/add?id=WBYX1TLPRWJ7NSV36LCPP2OZFH6AE6LM&user=elonmusk&secret=3dfb3e37b62f0f13ceca0dfa87a860b007a29e73"
//curl -i -H "Accept:application/json" "http://yii-twitter/users/feed?id=WBYX1TLPRWJ7NSV36LCPP2OZFH6AE6LM&secret=8500cd0d00463337bbd0908f6569f766ae54218c"
//curl -i -H "Accept:application/json" "http://yii-twitter/users/remove?id=WBYX1TLPRWJ7NSV36LCPP2OZFH6AE6LM&user=elonmusk&secret=3dfb3e37b62f0f13ceca0dfa87a860b007a29e73"

class UserController extends ActiveController
{
	public $modelClass = 'app\models\User';

	private static $settings = array(
		'oauth_access_token' => "1113400947470950401-uqAC4L1sbfNf5sZxSo4Sg1Zd282u4L",
		'oauth_access_token_secret' => "aCcqUOTVc6vpsJ2YeIiE7egkaF5QGU2AeTmS4k4r9hfr0",
		'consumer_key' => "sCxe33EMsMpo0bp3hbTr0k4KL",
		'consumer_secret' => "Ai6z2oY1cPfUf1ZMKGxYDxodkFBjpfodsjR5ZPpn2WAvbCIfG3"
	);
	 
	public function actionAdd()
	{

		$params = \Yii::$app->request->get();
		$response = \Yii::$app->response;

		$secret = sha1($params['id'].$params['user']);

		if(isset($params['id']) && isset($params['user']) && isset($params['secret']))
		{
			if($secret === $params['secret'])
			{
				$model = new User;
				$model->scenario = User::SCENARIO_CREATE;
				$model->screen_name = $params['user'];   

				if($model->validate())
				{
					$model->save();

					$response->statusCode = 200;
					$response->data = [''];
					return $response;
				}
				else
				{
					$response->statusCode = 500;
					$response->data = ['error' => 'internal error'];
					return $response;
				}
			}
			else
			{
				$response->statusCode = 403;
				$response->data = ['error' => 'access denied'];
				return $response;
			}
		}
		else
		{
			$response->statusCode = 400;
			$response->data = ['error' => 'missing parameter'];
			return $response;  
		}

	}

	public function actionFeed()
	{

		$params = \Yii::$app->request->get();
		$response = \Yii::$app->response;

		$secret = sha1($params['id']);

		if(isset($params['id']) && isset($params['secret']))
		{
			if($secret === $params['secret'])
			{
				$users = User::find()->select('screen_name')->all();

				if(empty($users))
				{
					$response->statusCode = 200;
					$response->data = [''];
					return $response;
				}
				else
				{			
					$screenNames = [];
					foreach($users as $user)
					{
						$screenNames[] = $user->screen_name;
					}

					$tweets = self::getUsersTweets($screenNames);

					if($tweets !== null)
					{
						$response->statusCode = 200;
						$response->data = ['feed' => $tweets];
						return $response;
					}
					else
					{
						$response->statusCode = 500;
						$response->data = ['error' => 'internal error'];
						return $response;
					}
				}
			}
			else
			{
				$response->statusCode = 403;
				$response->data = ['error' => 'access denied'];
				return $response;
			}
		}
		else
		{
			$response->statusCode = 400;
			$response->data = ['error' => 'missing parameter'];
			return $response;  
		}
	}

	public function actionRemove()
	{

		$params = \Yii::$app->request->get();
		$response = \Yii::$app->response;

		$secret = sha1($params['id'].$params['user']);

		if(isset($params['id']) && isset($params['user']) && isset($params['secret']))
		{
			if($secret === $params['secret'])
			{

				$userRecord = User::find()->where(['screen_name' => $params['user'] ])->one();  

				if(empty($userRecord))
				{
					$response->statusCode = 500;
					$response->data = ['error' => 'internal error'];
					return $response;
				}
				else
				{			
					$userRecord->delete();

					$response->statusCode = 200;
					$response->data = [''];
					return $response;
				}	
			}
			else
			{
				$response->statusCode = 403;
				$response->data = ['error' => 'access denied'];
				return $response;
			}
		}
		else
		{
			$response->statusCode = 400;
			$response->data = ['error' => 'missing parameter'];
			return $response;  
		}

	}

	private static function getUsersTweets(array $screenNames) {
		
		$tweetsLimit = 5;
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$requestMethod = 'GET';

		$twitter = new TwitterAPIExchange(self::$settings);

		$userTweets = array();
		$tweetsCount = 0;

		foreach($screenNames as $number => $user) {

			$getfield = "?screen_name={$user}&count={$tweetsLimit}&exclude_replies=true&include_rts=false";

			try {
				$userTimeline = $twitter
				->setGetfield($getfield)
				->buildOauth($url, $requestMethod)
				->performRequest();
			} catch (ErrorException $e) {
				return null;
			}

			$userTimeline = json_decode($userTimeline);

			foreach($userTimeline as $k => $userTweet) {
				$userTweets[$tweetsCount]['user'] = $userTweet->user->name;
				$userTweets[$tweetsCount]['tweet'] = $userTweet->text;
				$hashtags = $userTweet->entities->hashtags;
				foreach($hashtags as $hashtag) {
					$userTweets[$tweetsCount]['hashtag'][] = $hashtag->text;
				}
				$tweetsCount++;
			}
			
		}

		return $userTweets;
	}
}
