<?php

namespace Library;

abstract class BaseMiddleware extends BaseHttp
{
  abstract function handle();
}
