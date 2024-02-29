<?php

namespace App\Repositories;

use App\Models\UnitItem;
use App\Repositories\BaseRepository;

/**
 * Class UnitItemRepository
 * @package App\Repositories
 * @version November 13, 2021, 1:58 pm UTC
*/

class UnitItemRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'unit_pembangkit_id',
        'name'
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
        return UnitItem::class;
    }
}
