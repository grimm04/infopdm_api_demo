<?php

namespace App\Repositories;

use App\Models\Equipment;
use App\Repositories\BaseRepository;

/**
 * Class EquipmentRepository
 * @package App\Repositories
 * @version November 3, 2021, 1:30 am UTC
*/

class EquipmentRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'unit_pembangkit_id'

    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Equipment::class;
    }
}
