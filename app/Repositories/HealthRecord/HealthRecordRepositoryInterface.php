<?php

namespace App\Repositories\HealthRecord;

/**
 * Interface HealthRecordRepositoryInterface
 *
 * @package App\Repositories\HealthRecord
 */
interface HealthRecordRepositoryInterface
{
    /**
     * Find a health record by its ID.
     *
     * @param int $id
     * @return \App\Models\HealthRecord|null
     */
    public function find($id);

    /**
     * Create a new health record.
     *
     * @param array $data
     * @return \App\Models\HealthRecord
     */
    public function create(array $data);

    /**
     * Update an existing health record by its ID.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\HealthRecord|null
     */
    public function update($id, array $data);

    /**
     * Delete a health record by its ID.
     *
     * @param int $id
     * @return \App\Models\HealthRecord|null
     */
    public function delete($id);
}
