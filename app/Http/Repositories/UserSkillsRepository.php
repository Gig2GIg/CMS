<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-29
 * Time: 08:56
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IUserSkillsRepository;
use App\Models\UserSkills;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class UserSkillsRepository implements IUserSkillsRepository
{
    protected $model;
    protected $log;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(UserSkills $model)
    {
        $this->model = $model;
        $this->log = new LogManger();
    }


    public function create(array $data): UserSkills
    {


        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): UserSkills
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }


    public function findbyparam($colum, $value):?Collection
    {
        try{

            return $this->model->with('skills')->where($colum,'=',$value)->get();
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


    public function delete(): ?bool
    {
        return $this->model->delete();
    }

    public function all()
    {
        return $this->model->all();
    }
}
