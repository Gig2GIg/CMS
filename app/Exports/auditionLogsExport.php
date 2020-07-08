<?php

namespace App\Exports;

use App\Models\AuditionLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Http\Resources\AuditionLogResource;

class auditionLogsExport implements FromCollection, WithHeadings
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

	public function headings(): array
    {
        return [
            'key Name',
            'Old Value',
            'New Value',
            'Edited By User',
            'Time Of Edit'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $return = AuditionLog::select('id', 'key', 'old_value', 'new_value', 'edited_by', 'created_at')->where('audition_id', $this->data)->get();

        $response = AuditionLogResource::collection($return);
        
        return $response;
    }
}
