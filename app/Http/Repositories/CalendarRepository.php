<?php

namespace App\Http\Repositories;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Interfaces\ICalendarRepository;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class CalendarRepository implements ICalendarRepository
{
    protected $model;
    protected $log;

    /**
     * CalendarRepository constructor.
     * @param Calendar $calendar
     */
    public function __construct(Calendar $calendar)
    {
        $this->model = $calendar;
        $this->log = new LogManger();
    }


    public function create(array $data): Calendar
    {
        try {
            return $this->model->create($data);
            $this->log->info($this->model);
        } catch (QueryException $e) {
            $this->log->error('ERROR' . $e->getMessage(), class_basename($this));
            throw new CreateException($e);
        }
    }

    public function betweenDates($start_date,$end_date,$user_id)
    {
        return $this->model->where(function ($query) use ($start_date, $end_date,$user_id) {

            $query->where(function ($q) use ($start_date, $end_date,$user_id) {
                $q->where('start_date', '>=', $start_date)
                   ->where('start_date', '<', $end_date)
                   ->where('user_id', '=', $user_id);
        
            })->orWhere(function ($q) use ($start_date, $end_date,$user_id) {
                $q->where('start_date', '<=', $start_date)
                   ->where('end_date', '>', $end_date)
                   ->where('user_id', '=', $user_id);
        
            })->orWhere(function ($q) use ($start_date, $end_date,$user_id) {
                $q->where('end_date', '>=', $start_date)
                   ->where('end_date', '<=', $end_date)
                   ->where('user_id', '=', $user_id);
        
            })->orWhere(function ($q) use ($start_date, $end_date,$user_id) {
                $q->where('start_date', '>=', $start_date)
                   ->where('end_date', '<=', $end_date)
                   ->where('user_id', '=', $user_id);
            });
        
        })->count();

    }

    public function find($id): Calendar
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
        
    }

    public function all()
    {
        return $this->model->all();
    }

    public function orderBy($column,$value)
    {
        return $this->model->orderBy($column,$value)->get();
    }

}
