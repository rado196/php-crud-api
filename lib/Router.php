<?php

namespace Library;

final class Router
{
  private static $groupPrefix = '';
  private static $routes = [];

  private static function pathToPattern($path)
  {
    return preg_replace(['#\:[a-zA-Z0-9]+#'], ['([a-zA-Z0-9]+)'], $path);
  }

  private static function register($method, $path, $controller, $action, $middlewares = [])
  {
    self::$routes[] = [
      'method' => $method,
      'path' => self::pathToPattern(self::$groupPrefix . $path),
      'controller' => $controller,
      'action' => $action,
      'middlewares' => $middlewares,
    ];
  }

  public static function get($path, $controller, $action, $middlewares = [])
  {
    self::register('GET', $path, $controller, $action, $middlewares);
  }

  public static function post($path, $controller, $action, $middlewares = [])
  {
    self::register('POST', $path, $controller, $action, $middlewares);
  }

  public static function put($path, $controller, $action, $middlewares = [])
  {
    self::register('PUT', $path, $controller, $action, $middlewares);
  }

  public static function delete($path, $controller, $action, $middlewares = [])
  {
    self::register('DELETE', $path, $controller, $action, $middlewares);
  }

  public static function resource($path, $controller, $middlewares = [])
  {
    self::get($path, $controller, 'getAll', $middlewares);
    self::get($path . '/:id', $controller, 'getById', $middlewares);
    self::post($path, $controller, 'create', $middlewares);
    self::put($path . '/:id', $controller, 'update', $middlewares);
    self::delete($path . '/:id', $controller, 'delete', $middlewares);
  }

  public static function group($prefix, $callback)
  {
    $oldPrefix = self::$groupPrefix;
    self::$groupPrefix .= $prefix;

    $callback();

    self::$groupPrefix = $oldPrefix;
  }

  public static function handle()
  {
    $currentUrl = $_SERVER['REQUEST_URI'];
    // echo '<pre>';
    // var_dump(self::$routes);
    // die;

    foreach (self::$routes as $route) {
      if ($route['method'] !== $_SERVER['REQUEST_METHOD']) {
        continue;
      }

      $matches = [];

      $pattern = '#' . $route['path'] . '$#';
      if (preg_match($pattern, $currentUrl, $matches)) {
        array_shift($matches);

        // execute middlewares
        foreach($route['middlewares'] as $middleware) {
          $middleware = 'App\\Middlewares\\' . $middleware;
          $middleware = new $middleware();

          $middleware->handle();
        }

        // execute controller
        $controller = 'App\\Controllers\\' . $route['controller'];
        $controller = new $controller();

        $response = call_user_func_array([$controller, $route['action']], $matches);

        header('content-type: application/json');
        echo json_encode($response);
        exit;
      }
    }

    echo '404';
  }
}
