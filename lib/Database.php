<?php

namespace Library;

use PDO;
use RuntimeException;

abstract class Database
{
  private static $pdo = null;

  public function __construct()
  {
    if (is_null(self::$pdo)) {
      $connectionString =
        'pgsql:' .
        'host=' . env('DB_HOSTNAME') . ';' .
        'port=' . env('DB_PORT') . ';' .
        'dbname=' . env('DB_DATABASE') . ';' .
        'user=' . env('DB_USERNAME') . ';' .
        'password=' . env('DB_PASSWORD');

      self::$pdo = new PDO($connectionString);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
  }

  private function appendTimestamps(
    &$sqlFieldsPart,
    &$sqlDataPart,
    &$bindingData,
    $timestampFields
  ) {
    $nowTimestamp = date('Y-m-d H:i:s.vP');
    foreach ($timestampFields as $field) {
      $sqlFieldsPart[] = '"' . $field . '"';
      $sqlDataPart[] = ':' . $field;
      $bindingData[$field] = $nowTimestamp;
    }
  }

  protected final function executeInsert($table, $fields, $attributes)
  {
    $sqlFieldsPart = [];
    $sqlDataPart = [];

    $bindingData = [];
    foreach ($attributes as $field => $value) {
      if (in_array($field, $fields)) {
        $sqlFieldsPart[] = '"' . $field . '"';
        $sqlDataPart[] = ':' . $field;
        $bindingData[$field] = $value;
      }
    }

    if (empty($sqlFieldsPart)) {
      throw new RuntimeException('Invalid attributes provided.');
    }

    $this->appendTimestamps(
      $sqlFieldsPart,
      $sqlDataPart,
      $bindingData,
      ['created_at', 'updated_at']
    );

    $sql = 'INSERT INTO "' . $table . '" (' . implode(', ', $sqlFieldsPart) . ') '
      . 'VALUES (' . implode(', ', $sqlDataPart) . ')';

    $statement = self::$pdo->prepare($sql);
    $statement->execute($bindingData);
    return self::$pdo->lastInsertId();
  }

  protected final function executeUpdate($instanceId, $table, $fields, $attributes)
  {
    $sqlFieldsPart = [];
    $sqlDataPart = [];

    $bindingData = [];
    foreach ($attributes as $field => $value) {
      if (in_array($field, $fields)) {
        $sqlFieldsPart[] = '"' . $field . '"';
        $sqlDataPart[] = ':' . $field;
        $bindingData[$field] = $value;
      }
    }

    if (empty($sqlFieldsPart)) {
      return;
    }

    $this->appendTimestamps(
      $sqlFieldsPart,
      $sqlDataPart,
      $bindingData,
      ['updated_at']
    );

    $sqlUpdateCommand = [];

    $updatableFieldsCount = count($sqlDataPart);
    for ($i = 0; $i < $updatableFieldsCount; ++$i) {
      $sqlUpdateCommand[] = $sqlFieldsPart[$i] . '=' . $sqlDataPart[$i];
    }

    $sql = 'UPDATE "' . $table . '" SET ' . implode(', ', $sqlUpdateCommand) . ' '
      . 'WHERE "id"=:id';

    $bindingData['id'] = $instanceId;

    $statement = self::$pdo->prepare($sql);
    $statement->execute($bindingData);
  }

  protected final function executeDelete($instanceId, $table)
  {
    $sql = 'DELETE FROM "' . $table . '" WHERE "id"=:id';
    $bindingData = ['id' => $instanceId];

    $statement = self::$pdo->prepare($sql);
    $statement->execute($bindingData);
  }

  protected final function executeFindById($instanceId, $table, $fields)
  {
    $sqlFieldsPart = ['"id"', '"created_at"', '"updated_at"'];
    foreach ($fields as $field) {
      $sqlFieldsPart[] = '"' . $field . '"';
    }

    $sql = 'SELECT ' . implode(', ', $sqlFieldsPart) . ' ' .
      'FROM "' . $table . '" ' .
      'WHERE "id"=:id ' .
      'LIMIT 1';

    $bindingData = ['id' => $instanceId];

    $statement = self::$pdo->prepare($sql);
    $statement->execute($bindingData);

    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  // @TODO: add pagination, ordering, filtering functionality
  protected final function executeFindAll($table, $fields, $filter)
  {
    $sqlFieldsPart = ['"id"', '"created_at"', '"updated_at"'];
    foreach ($fields as $field) {
      $sqlFieldsPart[] = '"' . $field . '"';
    }

    $sql = 'SELECT ' . implode(', ', $sqlFieldsPart) . ' ' .
      'FROM "' . $table . '"';

    if (is_null($filter) || !is_array($filter)) {
      $filter = [];
    }

    if (!empty($filter)) {
      $sqlFieldsFilter = [];
      foreach ($filter as $key => $value) {
        $sqlFieldsFilter[] = '"' . $key . '"=:' . $key . '';
      }

      $sql .= ' WHERE ' . implode(' AND ', $sqlFieldsFilter);
    }

    $statement = self::$pdo->prepare($sql);
    $statement->execute($filter);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }
}
