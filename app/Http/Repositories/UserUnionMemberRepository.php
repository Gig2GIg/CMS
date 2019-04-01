<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-11
 * Time: 14:51
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IUnionMember;
use App\Models\UserUnionMembers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

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
            throw new CreateException($e);
        }
    }

    public function find($id): UserUnionMembers
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException($e);
        }
    }
    public function findbyparam($colum, $value): Collection
    {
        try{

            return $this->model->where($colum,'=',$value)->get();
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }

    public function update(array $data):bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UpdateException($e);
        }
    }

    public function delete():?bool
    {
        return $this->model->delete();
    }
}
