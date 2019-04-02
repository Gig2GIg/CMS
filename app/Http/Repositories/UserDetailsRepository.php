<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-11
 * Time: 11:49
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IDetailsUserRepository;
use App\Models\UserDetails;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UserDetailsRepository implements IDetailsUserRepository
{
    protected  $model;
    protected $log;

    /**
     * UserDetailsRepository constructor.
     * @param UserDetails $details
     */
    public function __construct(UserDetails $details)
    {
        $this->model = $details;
        $this->log = new LogManger();
    }
    public function all()
    {
       return $this->model->all();
    }


    /**
     * @param array $data
     * @return UserDetails
     * @throws CreateException
     */
    public function create(array $data) : UserDetails
    {
        try {
            return $this->model->create($data);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    /**
     * @param $id
     * @return UserDetails
     * @throws NotFoundException
     */
    public function find($id): UserDetails
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException($e);
        }
    }


    /**
     * @param array $data
     * @return bool
     * @throws UpdateException
     */
    public function update(array $data): bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UpdateException($e);
        }
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function delete():?bool
    {
        return $this->model->delete();
    }

    public function findbyparam($colum, $value):?UserDetails
    {
        try{

            return $this->model->where($colum,'=',$value)->first();
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }
}
