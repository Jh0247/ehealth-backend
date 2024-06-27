<?php

namespace App\Facades;

use App\Builders\HealthRecordBuilder;
use App\Repositories\HealthRecord\HealthRecordRepositoryInterface;

/**
 * Class HealthRecordFacade
 *
 * @package App\Facades
 */
class HealthRecordFacade
{
    /**
     * @var HealthRecordBuilder
     */
    protected $healthRecordBuilder;

    /**
     * @var HealthRecordRepositoryInterface
     */
    protected $healthRecordRepository;

    /**
     * HealthRecordFacade constructor.
     *
     * @param HealthRecordBuilder $healthRecordBuilder
     * @param HealthRecordRepositoryInterface $healthRecordRepository
     */
    public function __construct(HealthRecordBuilder $healthRecordBuilder, HealthRecordRepositoryInterface $healthRecordRepository)
    {
        $this->healthRecordBuilder = $healthRecordBuilder;
        $this->healthRecordRepository = $healthRecordRepository;
    }

    /**
     * Create a health record for a user.
     *
     * @param int $userId
     * @param string|null $healthCondition
     * @param string|null $bloodType
     * @param string|null $allergic
     * @param string|null $diseases
     * @return \App\Models\HealthRecord
     */
    public function createHealthRecordForUser($userId, $healthCondition = null, $bloodType = null, $allergic = null, $diseases = null)
    {
        $healthRecordData = $this->healthRecordBuilder
            ->setUserId($userId)
            ->setHealthCondition($healthCondition)
            ->setBloodType($bloodType)
            ->setAllergic($allergic)
            ->setDiseases($diseases)
            ->build();

        return $this->healthRecordRepository->create($healthRecordData);
    }

    /**
     * Update a health record.
     *
     * @param int $id
     * @param string|null $healthCondition
     * @param string|null $bloodType
     * @param string|null $allergic
     * @param string|null $diseases
     * @return \App\Models\HealthRecord|null
     */
    public function updateHealthRecord($id, $healthCondition = null, $bloodType = null, $allergic = null, $diseases = null)
    {
        $healthRecordData = $this->healthRecordBuilder
            ->setHealthCondition($healthCondition)
            ->setBloodType($bloodType)
            ->setAllergic($allergic)
            ->setDiseases($diseases)
            ->build();

        return $this->healthRecordRepository->update($id, array_filter($healthRecordData));
    }
}
