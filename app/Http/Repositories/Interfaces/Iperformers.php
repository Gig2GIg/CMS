<?php


namespace App\Http\Repositories\Interfaces;


interface Iperformers
{  public function all();
    public function create(array $data);
    public function find($id);
    public function delete();
    public function findbyParam($column, $value);

}
