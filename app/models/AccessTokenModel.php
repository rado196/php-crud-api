<?php

namespace App\Models;

use Library\BaseModel;

class AccessTokenModel extends BaseModel
{
  protected $table = 'access_tokens';

  protected $fields = [
    'token',
  ];
}
