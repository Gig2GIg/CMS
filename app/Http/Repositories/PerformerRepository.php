<?php


namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\Interfaces\Iperformers;
use App\Models\Performers;
use App\Models\Slots;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class PerformerRepository implements Iperformers
{
    protected $model;
    protected $log;


    public function __construct(Performers $performer)
    {
        $this->model = $performer;
        $this->log = new LogManger();
    }


    public function create(array $data): Performers
    {


        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): Slots
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }


    public function findbyparam($colum, $value)
    {
        try{

            return $this->model->where($colum,'=',$value);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
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
