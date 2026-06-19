<?php
declare(strict_types=1);

namespace app\model;

class AdminUser extends BaseModel
{
    protected $name = 'admin_users';

    protected $hidden = [
        'password',
    ];
}

