<?php

namespace App\Facades;

use App\Builders\HealthRecordBuilder;
use App\Repositories\HealthRecord\HealthRecordRepositoryInterface;

class HealthRecordFacade
{
    protected $healthRecordBuilder;
    protected $healthRecordRepository;

    public function __construct(HealthRecordBuilder $healthRecordBuilder, HealthRecordRepositoryInterface $healthRecordRepository)
    {
        $this->healthRecordBuilder = $healthRecordBuilder;
        $this->healthRecordRepository = $healthRecordRepository;
    }

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
}
