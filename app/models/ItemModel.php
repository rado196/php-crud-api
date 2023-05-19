<?php

namespace App\Models;

use Library\BaseModel;

class ItemModel extends BaseModel
{
  protected $table = 'items';

  protected $fields = [
    'name',
    'phone',
    'key',
  ];
}
