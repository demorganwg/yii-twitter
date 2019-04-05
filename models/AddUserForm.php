<?php

namespace app\models;

use yii\base\Model;

class AddUserForm extends Model
{
    public $user;

    public function rules()
    {
        return [
            [['user'], 'required']
        ];
    }
}