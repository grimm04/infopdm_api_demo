<?php

namespace App\Repositories;

use App\Models\UserRole;
use App\Repositories\BaseRepository;

/**
 * Class UserRoleRepository
 * @package App\Repositories
 * @version November 8, 2021, 7:33 am UTC
*/

class UserRoleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [ 
        'name',
        'status',
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
        return UserRole::class;
    }
}
