<?php


namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\Interfaces\IMonitorRepository;
use App\Models\Monitor;
use App\Models\Roles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class MonitorRepository implements IMonitorRepository
{
    protected $model;
    protected $log;

    public function __construct(Monitor $model)
    {
        $this->model = $model;
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

    public function find($id):Monitor
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException($e);
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
}
