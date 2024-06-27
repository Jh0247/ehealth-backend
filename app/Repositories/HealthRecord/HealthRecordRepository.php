<?php

namespace App\Repositories\HealthRecord;

use App\Models\HealthRecord;

/**
 * Class HealthRecordRepository
 *
 * @package App\Repositories\HealthRecord
 */
class HealthRecordRepository implements HealthRecordRepositoryInterface
{
    /**
     * Find a health record by its ID.
     *
     * @param int $id
     * @return HealthRecord|null
     */
    public function find($id)
    {
        return HealthRecord::find($id);
    }

    /**
     * Create a new health record.
     *
     * @param array $data
     * @return HealthRecord
     */
    public function create(array $data)
    {
        return HealthRecord::create($data);
    }

    /**
     * Update an existing health record by its ID.
     *
     * @param int $id
     * @param array $data
     * @return HealthRecord|null
     */
    public function update($id, array $data)
    {
        $record = HealthRecord::find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    /**
     * Delete a health record by its ID.
     *
     * @param int $id
     * @return HealthRecord|null
     */
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
