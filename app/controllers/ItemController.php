<?php

namespace App\Controllers;

use App\Models\ItemModel;
use Library\BaseController;

class ItemController extends BaseController
{
  /**
   * @route GET /api/items
   */
  public function getAll()
  {
    $items = ItemModel::findAll();
    return ['items' => $items];
  }

  /**
   * @route GET /api/items/:id
   */
  public function getById($id)
  {
    $item = ItemModel::findById($id);
    return ['item' => $item];
  }

  /**
   * @route POST /api/items
   */
  public function create()
  {
    $item = new ItemModel();
    $item->name = $this->param('name');
    $item->phone = $this->param('phone');
    $item->key = $this->param('key');
    $item->save();

    return ['id' => $item->id];
  }

  /**
   * @route PUT /api/items/:id
   */
  public function update($id)
  {
    $item = ItemModel::findById($id);
    $item->name = $this->param('name');
    $item->phone = $this->param('phone');
    $item->save();

    return ['id' => $item->id];
  }

  /**
   * @route DELETE /api/items/:id
   */
  public function delete($id)
  {
    $item = ItemModel::findById($id);
    $item->delete();

    return ['id' => $item->id];
  }
}
