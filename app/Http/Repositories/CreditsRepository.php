<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-27
 * Time: 17:24
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\ICreditsRepository;
use App\Models\Credits;
use Illuminate\Database\QueryException;

class CreditsRepository implements ICreditsRepository
{
    protected $model;
    protected $log;

    /**
     * UserRepository constructor.
     * @param User $user
     */
    public function __construct(Credits $credits)
    {
        $this->model = $credits;
        $this->log = new LogManger();
    }


    public function create(array $data): Credits
    {


        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): Credits
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
