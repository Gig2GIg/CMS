<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-29
 * Time: 16:19
 */

namespace App\Http\Repositories\Interfaces;


interface IUserAparenceRepository
{
    public function create(array $data);
    public function find($id);
    public function update(array $data);
}
