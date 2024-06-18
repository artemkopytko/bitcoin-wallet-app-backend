<?php

declare(strict_types=1);

namespace App\Models;

use Laratrust\Models\Role as RoleModel;
use App\Enums\Permissions;
use App\Enums\Roles;

/**
 * @property string $name
 * @property string $display_name
 * @property string $description
*/
class Role extends RoleModel
{
    public $guarded = [];

    public static $rolePermissions = [
        Roles::ADMIN->value => [
            Permissions::USER_CREATE->value,
            Permissions::USER_EDIT->value,
            Permissions::USER_DELETE->value,
            Permissions::USER_READ->value,
            Permissions::USER_READ_SELF->value,
            Permissions::USER_EDIT_SELF->value,
            Permissions::WALLET_ADD->value,
            Permissions::WALLET_EDIT->value,
            Permissions::WALLET_DELETE->value,
            Permissions::WALLET_READ->value,
            Permissions::WITHDRAWAL_ADD->value,
            Permissions::WITHDRAWAL_EDIT->value,
            Permissions::WITHDRAWAL_DELETE->value,
            Permissions::WITHDRAWAL_READ->value,
            Permissions::DEPOSIT_ADD->value,
            Permissions::DEPOSIT_EDIT->value,
            Permissions::DEPOSIT_DELETE->value,
            Permissions::DEPOSIT_READ->value,
            Permissions::EVENT_READ->value,
            Permissions::STATISTICS_READ->value,
        ],
        Roles::MANAGER->value => [
            Permissions::USER_CREATE->value,
            Permissions::USER_EDIT->value,
            Permissions::USER_DELETE->value,
            Permissions::USER_READ->value,
            Permissions::USER_READ_SELF->value,
            Permissions::USER_EDIT_SELF->value,
            Permissions::WALLET_READ->value,
            Permissions::WITHDRAWAL_ADD->value,
            Permissions::WITHDRAWAL_EDIT->value,
            Permissions::WITHDRAWAL_DELETE->value,
            Permissions::WITHDRAWAL_READ->value,
            Permissions::DEPOSIT_ADD->value,
            Permissions::DEPOSIT_EDIT->value,
            Permissions::DEPOSIT_DELETE->value,
            Permissions::DEPOSIT_READ->value,
            Permissions::EVENT_READ->value
        ],
        Roles::USER->value => [
            Permissions::USER_READ_SELF->value,
            Permissions::USER_EDIT_SELF->value,
            Permissions::WALLET_READ->value,
            Permissions::WITHDRAWAL_ADD->value,
            Permissions::WITHDRAWAL_READ->value,
            Permissions::DEPOSIT_READ->value
        ],
    ];
}
