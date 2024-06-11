<?php

namespace App\Builders;

class HealthRecordBuilder
{
    protected $attributes = [];

    public function setUserId($userId)
    {
        $this->attributes['user_id'] = $userId;
        return $this;
    }

    public function setHealthCondition($healthCondition)
    {
        $this->attributes['health_condition'] = $healthCondition;
        return $this;
    }

    public function setBloodType($bloodType)
    {
        $this->attributes['blood_type'] = $bloodType;
        return $this;
    }

    public function setAllergic($allergic)
    {
        $this->attributes['allergic'] = $allergic;
        return $this;
    }

    public function setDiseases($diseases)
    {
        $this->attributes['diseases'] = $diseases;
        return $this;
    }

    public function build()
    {
        return $this->attributes;
    }
}
