<?php

namespace App\Exports;

use App\Models\TempUserImportedList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class importedUserExport implements FromCollection, WithHeadings
{

	public function headings(): array
    {
        return [
            '#',
            'Email',
            'Password'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TempUserImportedList::all();
    }
}
