<?php

namespace Library;

use Throwable;

abstract class BaseHttp
{
  private $parsedHeaders = null;
  private $parsedBody = null;

  protected final function getHeader($key, $default = null)
  {
    try {
      $key = strtolower($key);
      if (is_null($this->parsedHeaders)) {
        $this->parsedHeaders = [];
        foreach ($_SERVER as $headerKey => $value) {
          if (substr($headerKey, 0, 5) !== 'HTTP_') {
            continue;
          }

          $headerKey = strtolower(substr($headerKey, 5));
          $headerKey = str_replace(['_', ' '], '-', $headerKey);
          $this->parsedHeaders[$headerKey] = $value;
        }
      }

      return isset($this->parsedHeaders[$key])
        ? $this->parsedHeaders[$key]
        : $default;
    } catch (Throwable $e) {
      return $default;
    }
  }

  protected final function param($key, $default = null)
  {
    if (isset($_REQUEST[$key])) {
      return $_REQUEST[$key];
    }

    try {
      if (is_null($this->parsedBody)) {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        $this->parsedBody = $request;
      }

      return isset($this->parsedBody[$key])
        ? $this->parsedBody[$key]
        : $default;
    } catch (Throwable $e) {
      return $default;
    }
  }

  protected final function httpStatus($code)
  {
    header('HTTP/1.1 ' . $code);
    return $this;
  }

  protected final function jsonResponse($data)
  {
    header('content-type: application/json');
    echo json_encode($data);
    exit;
  }

  protected final function errorResponse($code, $message)
  {
    return $this->httpStatus($code)->jsonResponse(['error' => $message]);
  }
}
