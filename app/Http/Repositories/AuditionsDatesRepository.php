<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-15
 * Time: 17:05
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IAuditionsDatesRespository;
use App\Models\Dates;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class AuditionsDatesRepository implements IAuditionsDatesRespository
{
    protected $model;
    protected $log;

    public function __construct(Dates $auditionsDate)
    {
        $this->model = $auditionsDate;
        $this->log = new LogManger();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }

    public function find($id):Dates
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException($e);
        }
    }

    public function update(array $data): bool
    {
        try{
            return $this->model->update($data);
        }catch (QueryException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new UpdateException($e);
        }
    }

    public function delete():?bool
    {
        return $this->model->delete();
    }

}
