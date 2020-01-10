<?php

namespace App\Http\Repositories;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\Interfaces\IUserSlotsReporitory;
use App\Models\UserSlots;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class UserSlotsRepository implements IUserSlotsReporitory
{
    protected $model;
    protected $log;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(UserSlots $model)
    {
        $this->model = $model;
        $this->log = new LogManger();
    }

    public function create(array $data): UserSlots
    {

        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }

    public function find($id): UserSlots
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }

    public function findbyparam($colum, $value): ?Collection
    {
        try {

            return $this->model->where($colum, '=', $value)->get();
        } catch (ModelNotFoundException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }

    public function update(array $data)
    {
        // TODO: Implement update() method.
    }

    public function all()
    {
        return $this->model->all();
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
