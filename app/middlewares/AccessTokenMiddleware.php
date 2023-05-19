<?php

namespace App\Middlewares;

use App\Models\AccessTokenModel;
use Library\BaseMiddleware;

class AccessTokenMiddleware extends BaseMiddleware
{
  public function handle()
  {
    $token = $this->getHeader('authorization');
    if (!$token) {
      $this->errorResponse(401, 'Access token required.');
    }

    $token = preg_replace('#^bearer #im', '', $token);
    $accessTokens = AccessTokenModel::findAll(['token' => $token]);

    if (empty($accessTokens)) {
      $this->errorResponse(401, 'Access token not found.');
    }
  }
}
