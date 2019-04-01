<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-29
 * Time: 16:20
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IUserAparenceRepository;
use App\Models\UserAparence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UserAparenceRepository implements IUserAparenceRepository
{
    protected $model;
    protected $log;

    public function __construct(UserAparence $model)
    {
        $this->model = $model;
        $this->log = new LogManger();
    }


    public function create(array $data): UserAparence
    {


        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): UserAparence
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }

    public function findbyparam($colum, $value):?UserAparence
    {
        try{

            return $this->model->where($colum,'=',$value)->first();
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }



    public function update(array $data) : bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UpdateException($e);
        }
    }

}
