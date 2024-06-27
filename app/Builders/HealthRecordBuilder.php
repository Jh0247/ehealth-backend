<?php

namespace App\Builders;

/**
 * Class HealthRecordBuilder
 *
 * @package App\Builders
 */
class HealthRecordBuilder
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Set the user ID for the health record.
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->attributes['user_id'] = $userId;
        return $this;
    }

    /**
     * Set the health condition for the health record.
     *
     * @param string|null $healthCondition
     * @return $this
     */
    public function setHealthCondition($healthCondition)
    {
        $this->attributes['health_condition'] = $healthCondition;
        return $this;
    }

    /**
     * Set the blood type for the health record.
     *
     * @param string|null $bloodType
     * @return $this
     */
    public function setBloodType($bloodType)
    {
        $this->attributes['blood_type'] = $bloodType;
        return $this;
    }

    /**
     * Set the allergies for the health record.
     *
     * @param string|null $allergic
     * @return $this
     */
    public function setAllergic($allergic)
    {
        $this->attributes['allergic'] = $allergic;
        return $this;
    }

    /**
     * Set the diseases for the health record.
     *
     * @param string|null $diseases
     * @return $this
     */
    public function setDiseases($diseases)
    {
        $this->attributes['diseases'] = $diseases;
        return $this;
    }

    /**
     * Build the health record attributes.
     *
     * @return array
     */
    public function build()
    {
        return $this->attributes;
    }
}
