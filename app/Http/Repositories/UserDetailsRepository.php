<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-11
 * Time: 11:49
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\UserDetails\UserDetailsCreateException;
use App\Http\Exceptions\UserDetails\UserDetailsNotFoundException;
use App\Http\Exceptions\UserDetails\UserDetailsUpdateException;
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
     * @throws UserDetailsCreateException
     */
    public function create(array $data) : UserDetails
    {
        try {
            return $this->model->create($data);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserDetailsCreateException($e);
        }
    }

    /**
     * @param $id
     * @return UserDetails
     * @throws UserDetailsNotFoundException
     */
    public function find($id): UserDetails
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserDetailsNotFoundException($e);
        }
    }

    /**
     * @param array $data
     * @return bool
     * @throws UserDetailsUpdateException
     */
    public function update(array $data): bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserDetailsUpdateException($e);
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
}
