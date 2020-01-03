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
use App\Http\Repositories\Interfaces\IPushKeysUserRepository;
use App\Models\UserPushKeys;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UserPushKeysRepository implements IPushKeysUserRepository
{
    protected $model;
    protected $log;

    /**
     * UserPushKeysRepository constructor.
     * @param UserPushKeys $details
     */
    public function __construct(UserPushKeys $details)
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
     * @return UserPushKeys
     * @throws CreateException
     */
    public function create(array $data): UserPushKeys
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
     * @return UserPushKeys
     * @throws NotFoundException
     */
    public function find($id): UserPushKeys
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
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
        try {
            return $this->model->update($data);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UpdateException($e);
        }
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function delete(): ?bool
    {
        return $this->model->delete();
    }

    public function findbyparam($colum, $value): ?UserPushKeys
    {
        try {

            return $this->model->where($colum, '=', $value)->first();
        } catch (ModelNotFoundException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }

    public function findbyparams($array)
    {
        try {

            return $this->model->where($array);
        } catch (ModelNotFoundException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }
}
