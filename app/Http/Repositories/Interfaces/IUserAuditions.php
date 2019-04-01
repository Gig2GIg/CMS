<?php


namespace App\Http\Repositories\Interfaces;


interface IUserAuditions
{
    public function create(array $data);
    public function find($id);
    public function getByParam($col,$value);
    public function update(array $data);
}
