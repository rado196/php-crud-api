<?php

namespace Library;

use JsonSerializable;

class BaseModel extends Database implements JsonSerializable
{
  protected $table = '';
  protected $fields = [];

  private $attributes = [];
  private $updatedFields = [];

  // #region getter/setter accessors
  public final function set($key, $value)
  {
    $this->attributes[$key] = $value;
    $this->updatedFields[$key] = [
      'old' => $this->attributes[$key],
      'new' => $value
    ];

    return $this;
  }

  public final function get($key)
  {
    return $this->attributes[$key];
  }

  public final function __set($key, $value)
  {
    return $this->set($key, $value);
  }

  public final function __get($key)
  {
    return $this->get($key);
  }

  public final function jsonSerialize(): mixed
  {
    $jsonData = [
      'id' => $this->attributes['id'],
      'created_at' => $this->attributes['created_at'],
      'updated_at' => $this->attributes['updated_at'],
    ];

    foreach ($this->fields as $field) {
      $jsonData[$field] = $this->attributes[$field];
    }

    return $jsonData;
  }
  // #endregion

  // #region updated field methods
  public final function changedFields()
  {
    return $this->updatedFields;
  }
  private function resetChanges()
  {
    $this->updatedFields = [];
  }
  // #endregion

  // #region mutation methods
  public final function create()
  {
    $insertedId = $this->executeInsert(
      $this->table,
      $this->fields,
      $this->attributes
    );

    $this->set('id', $insertedId);
    $this->resetChanges();
  }

  public final function update()
  {
    $this->executeUpdate(
      $this->get('id'),
      $this->table,
      $this->fields,
      $this->updatedFields
    );

    $this->resetChanges();
  }

  public final function save()
  {
    if ($this->id) {
      return $this->update();
    }

    return $this->create();
  }

  public final function delete()
  {
    $this->executeDelete(
      $this->get('id'),
      $this->table
    );
  }
  // #endregion

  // #region retrieval methods
  public static final function findById($id)
  {
    $that = new static();
    $result = $that->executeFindById(
      $id,
      $that->table,
      $that->fields
    );

    if (!$result) {
      return null;
    }

    $instance = new static();
    foreach ($result as $key => $value) {
      $instance->set($key, $value);
    }

    $instance->resetChanges();
    return $instance;
  }

  public static final function findAll($filter = [])
  {
    $that = new static();
    $results = $that->executeFindAll(
      $that->table,
      $that->fields,
      $filter
    );

    if (!$results) {
      return [];
    }

    $instances = [];
    foreach ($results as $result) {
      $instance = new static();
      foreach ($result as $key => $value) {
        $instance->set($key, $value);
      }

      $instance->resetChanges();
      $instances[] = $instance;
    }

    return $instances;
  }
  // #endregion
}
