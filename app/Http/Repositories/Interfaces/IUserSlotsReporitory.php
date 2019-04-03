<?php


namespace App\Http\Repositories\Interfaces;


interface IUserSlotsReporitory
{
    public function create(array $data);
    public function find($id);
    public function findbyParam($column, $value);
    public function update(array $data);
}
