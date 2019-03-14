<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-14
 * Time: 15:27
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Repositories\Interfaces\IAuditionsRepository;
use App\Models\Auditions;
use Illuminate\Database\QueryException;

class AuditionRepository implements IAuditionsRepository
{
    protected $model;
    protected $log;

    public function __construct(Auditions $auditions)
    {
        $this->model = $auditions;
        $this->log = new LogManger();
    }

    public function all()
    {
        // TODO: Implement all() method.
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

    public function find($id)
    {
        // TODO: Implement find() method.
    }

    public function update(array $data)
    {
        // TODO: Implement update() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }
}
