<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-11
 * Time: 14:51
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\UserUnionMembers\UserUnionCreateException;
use App\Http\Exceptions\UserUnionMembers\UserUnionNotFoundException;
use App\Http\Exceptions\UserUnionMembers\UserUnionUpdateException;
use App\Http\Repositories\Interfaces\IUnionMember;
use App\Models\UserUnionMembers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UserUnionMemberRepository implements IUnionMember
{
    protected $model;
    protected $log;

    /**
     * UnionMemberRepositor constructor.
     */
    public function __construct(UserUnionMembers $unionMember)
    {
        $this->model = $unionMember;
        $this->log = new LogManger();
    }

    public function all()
    {
       return $this->model->all();
    }

    public function create(array $data) : UserUnionMembers
    {
        try{
            return $this->model->create($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserUnionCreateException($e);
        }
    }

    public function find($id): UserUnionMembers
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserUnionNotFoundException($e);
        }
    }

    public function update(array $data):bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserUnionUpdateException($e);
        }
    }

    public function delete():?bool
    {
        return $this->model->delete();
    }
}
