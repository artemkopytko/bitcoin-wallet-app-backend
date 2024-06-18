<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laratrust\Models\Permission as PermissionModel;

/**
 * @property string $name
 * @property string $display_name
 * @property string $description
 *
 * @method static static|Builder wherePermissionNameIn(array $list)
*/

class Permission extends PermissionModel
{
    public $guarded = [];

    public static $permissions = [
        'create-user' => [
            'display_name' => 'Create User',
            'description' => 'Create User Permission',
        ],
        'update-user' => [
            'display_name' => 'Update User',
            'description' => 'Update User Permission',
        ],
        'delete-user' => [
            'display_name' => 'Delete User',
            'description' => 'Delete User Permission',
        ],
        'read-user' => [
            'display_name' => 'Read User',
            'description' => 'Read User Permission',
        ],
    ];

    public function scopeWherePermissionNameIn(Builder $builder, array $list): Builder
    {
        return $builder->where(
            static function (Builder $builder) use ($list): Builder {
                foreach ($list as $el) {
                    $builder = $builder->orWhere('name', $el);
                }

                return $builder;
            }
        );
    }
}
