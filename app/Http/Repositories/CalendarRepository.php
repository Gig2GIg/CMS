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

    public function find($id): Marketplace
    {
        

    }

    public function update(array $data) : bool
    {
        
    }

    /**
     * @return bool
     */
    public function delete(): ?bool
    {
        
    }

    public function all()
    {
        
    }

}
