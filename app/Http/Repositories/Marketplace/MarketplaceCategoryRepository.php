<?php
namespace App\Http\Repositories\Marketplace;


use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\Marketplace\IMarketplaceCategoryRepository;
use App\Models\MarketplaceCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class MarketplaceCategoryRepository implements IMarketplaceCategoryRepository
{
    protected $model;
    protected $log;


    public function __construct(MarketplaceCategory $makerplace_category)
    {
        $this->model = $makerplace_category;
        $this->log = new LogManger();
    }


    public function create(array $data): MarketplaceCategory
    {

        

        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }


    public function find($id): MarketplaceCategory
    {
        try{
            return $this->model->findOrFail($id);
        }catch (ModelNotFoundException $e){
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new NotFoundException("Not found Data");
        }

    }

    

    public function findbyparam($colum, $value): User
    {
        try{

            return $this->model->where($colum,'=',$value)->first();
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

    public function allMarketplace()
    {
      return $this->model->marketplaces;
    }
}
