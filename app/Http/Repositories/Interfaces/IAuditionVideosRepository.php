<?php


namespace App\Http\Repositories\Interfaces;


interface IAuditionVideosRepository
{
    public function create(array $data);
    public function find($id);
    public function findbyparam($column,$value);
    public function delete();
}
