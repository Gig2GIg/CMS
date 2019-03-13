<?php

namespace App\Http\Repositories\Interfaces;


interface IRepository
{
  public function all();
  public function create(array $data);
  public function find($id);
  public function update(array $data);
  public function delete();
}

