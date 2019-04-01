<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-28
 * Time: 14:34
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IEducationRepository;
use App\Models\Educations;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class EducationsRepository implements IEducationRepository
{
    protected $model;
    protected $log;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(Educations $educations)
    {
        $this->model = $educations;
        $this->log = new LogManger();
    }


    public function create(array $data): Educations
    {


        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): Educations
    {
        try{
            return $this->model->findOrFail($id);
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
