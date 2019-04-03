<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-18
 * Time: 10:11
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IAuditionsContributors;
use App\Models\Appointments;
use App\Models\AuditionContributors;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class AuditionContributorsRepository implements IAuditionsContributors
{
    protected $model;
    protected $log;
    public function __construct(AuditionContributors $contributors)
    {
        $this->model = $contributors;
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

    public function find($id):AuditionContributors
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

            return $this->model->where($colum,'=',$value)->get();
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
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
