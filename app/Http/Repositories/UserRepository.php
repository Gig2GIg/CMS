<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-06
 * Time: 13:45
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\User\DeleteUserException;
use App\Http\Exceptions\User\UpdateUserException;
use App\Http\Exceptions\User\UserCreateException;
use App\Http\Exceptions\User\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UserRepository
{
    protected $model;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * @param array $data
     * @return User
     * @throws UserCreateException
     */
    public function createUser(array $data): User
    {

        $log = new LogManger();
        try {
            return $this->model->create($data);
        } catch (QueryException $e) {
            $log->error('ERROR IN ' . class_basename($this) . "DESCRIPTION " . $e->getMessage());
            throw new UserCreateException($e);
        }
    }

    /**
     * @param $id
     * @return User
     * @throws UserNotFoundException
     */
    public function findUser($id): User
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
     * @throws UpdateUserException
     */
    public function updateUser(array $data) : bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            throw new UpdateUserException($e);
        }
    }

    /**
     * @return bool
     */
    public function deleteUser(): ?bool
    {
            return $this->model->delete();
    }
}
