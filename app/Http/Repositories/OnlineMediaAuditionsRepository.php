<?php


namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\IOnlineMediaAuditionsRepository;
use App\Models\OnlineMediaAudition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class OnlineMediaAuditionsRepository implements IOnlineMediaAuditionsRepository
{
    protected $model;
    protected $log;

    public function __construct(OnlineMediaAudition $auditionVideos)
    {
        $this->model = $auditionVideos;
        $this->log = new LogManger();
    }


    public function create(array $data): OnlineMediaAudition
    {

        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): OnlineMediaAudition
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }


    public function findbyparam($colum, $value)
    {
        try {

            return $this->model->where($colum, '=', $value);
        } catch (ModelNotFoundException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }
    public function delete(): ?bool
    {
        return $this->model->delete();
    }


}
