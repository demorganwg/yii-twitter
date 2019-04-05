<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
	const SCENARIO_CREATE = 'add';

	public static function tableName()
   {
        return '{{users}}';
   }

	public function rules()
	{
		return [
			['screen_name', 'required'],
			['screen_name', 'unique'],
			['screen_name', 'string', 'min' => 3],
		];
	}

	public function scenarios()
	{
		$scenarios = parent::scenarios();
		$scenarios['add'] = ['screen_name']; 
		return $scenarios; 
	}

	public function attributeLabels()
	{
		return [
			'screen_name' => 'User'
		];
	}
}