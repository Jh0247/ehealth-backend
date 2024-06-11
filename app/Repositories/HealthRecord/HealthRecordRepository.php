<?php

namespace App\Repositories\HealthRecord;

use App\Models\HealthRecord;

class HealthRecordRepository implements HealthRecordRepositoryInterface
{
    public function find($id)
    {
        return HealthRecord::find($id);
    }

    public function create(array $data)
    {
        return HealthRecord::create($data);
    }

    public function update($id, array $data)
    {
        $record = HealthRecord::find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete($id)
    {
        $record = HealthRecord::find($id);
        if ($record) {
            $record->delete();
            return $record;
        }
        return null;
    }
}
