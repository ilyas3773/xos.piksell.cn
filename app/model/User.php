<?php
declare(strict_types=1);

namespace app\model;

class User extends BaseModel
{
    protected $name = 'users';

    protected $hidden = [
        'password',
    ];

    protected $type = [
        'energy' => 'integer',
        'invite_count' => 'integer',
    ];
}
