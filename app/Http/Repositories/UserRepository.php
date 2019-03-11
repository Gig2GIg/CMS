<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-06
 * Time: 13:45
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\User\UserDeleteException;
use App\Http\Exceptions\User\UserUpdateException;
use App\Http\Exceptions\User\UserCreateException;
use App\Http\Exceptions\User\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UserRepository implements UserRepositoryInterface
{
    protected $model;
    protected $log;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->model = $user;
        $this->log = new LogManger();
    }

    /**
     * @param array $data
     * @return User
     * @throws UserCreateException
     */
    public function create(array $data): User
    {


        try {
            return $this->model->create($data);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UserCreateException($e);
        }
    }

    /**
     * @param $id
     * @return User
     * @throws UserNotFoundException
     */
    public function find($id): User
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            throw new UserNotFoundException($e);
        }

    }

    /**
     * @param array $data
     * @return bool
     * @throws UserUpdateException
     */
    public function update(array $data) : bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            throw new UserUpdateException($e);
        }
    }

    /**
     * @return bool
     */
    public function delete(): ?bool
    {
            return $this->model->delete();
    }

    public function all()
    {
      return $this->model->all();
    }
}
