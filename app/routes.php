<?php

use Library\Router;

Router::group('/api', function () {
  Router::resource('/items', 'ItemController', ['AccessTokenMiddleware']);
  // more routes here ...
});

Router::handle();
