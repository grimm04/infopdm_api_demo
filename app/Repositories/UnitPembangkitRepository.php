<?php

namespace App\Repositories;

use App\Models\UnitPembangkit;
use App\Repositories\BaseRepository;

/**
 * Class UnitPembangkitRepository
 * @package App\Repositories
 * @version November 7, 2021, 4:00 am UTC
*/

class UnitPembangkitRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'name',
        'address',
        'email',
        'status'
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
        return UnitPembangkit::class;
    }
}
