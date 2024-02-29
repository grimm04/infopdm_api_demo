<?php

namespace App\Repositories;

use App\Models\UserManagement;
use App\Repositories\BaseRepository;

/**
 * Class UserManagementRepository
 * @package App\Repositories
 * @version November 10, 2020, 8:45 pm WIB
*/

class UserManagementRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [ 
        'unit_pembangkit_id', 
        'nip',
        'status',
        'name',
        'username',
        'email',  
        'job',
        'office',  
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
        return UserManagement::class;
    }
}
