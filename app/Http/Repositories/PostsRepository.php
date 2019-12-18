<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-18
 * Time: 13:33
 */

namespace App\Http\Repositories;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;

use App\Http\Repositories\Interfaces\IPostsRepository;

use App\Models\Posts;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class PostsRepository implements IPostsRepository
{
    protected $model;
    protected $log;

    public function __construct(Posts $posts)
    {
        $this->model = $posts;
        $this->log = new LogManger();
    }

    public function create(array $data): Posts
    {
        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }

    public function find($id): Posts
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

    public function search_by_title($search)
    {
       $result =  $this->model->where('title', 'LIKE', "%{$search}%")
                    ->orderBy('title', 'desc')->get();

       return  $result;
    }
}
