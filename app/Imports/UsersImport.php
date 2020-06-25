<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserSubscription;
use App\Models\TempUserImportedList;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

use Carbon\Carbon;

class UsersImport implements ToModel, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $rows = 0;

    public function model(array $row)
    {   
        ++$this->rows;

        if($this->rows <= 2 || ($row[0] == NULL || $row[0] == ''))
        {
            return null;
        }

        $password = str_random(8);

        return new User([
            'email' => $row[0],
            'password' => bcrypt($password),
            'is_profile_completed' => 0,
            'is_premium' => 1,
            'temp_pass' => $password,
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
