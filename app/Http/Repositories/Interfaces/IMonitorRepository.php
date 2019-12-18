<?php


namespace App\Http\Repositories\Interfaces;


interface IMonitorRepository
{
    public function all();
    public function create(array $data);
    public function find($id);

}
