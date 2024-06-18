<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\DepositStatuses;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /** @var User */
    public $resource;

    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'avatar_url' => $this->resource->getAvatarUrl(),
            'is_active' => (bool) $this->resource->is_active,
            'is_2fa_enabled' =>  (bool) $this->resource->is_2fa_enabled,
            'is_email_verified' => $this->resource->email_verified_at !== null,
            'notes' => $this->resource->notes,
            'role' => $this->resource->getRawRoles() ? $this->resource->getRawRoles()[0] : null,
            'created_at' => (int)$this->resource->created_at->timestamp,
            'updated_at' => (int)$this->resource->updated_at->timestamp,
            'balance' => (float) $this->resource->balance, // in BTC
            'usd_balance' => $this->resource->usd_balance(),
            'eur_balance' => $this->resource->eur_balance(),
        ];

        if (!$user->hasRole('user')) {
            $data['deposit_sum'] = $this->resource->deposits->map(function ($deposit) {
                $acceptedStatuses = [
                    DepositStatuses::COMPLETED->value,
                    DepositStatuses::APPROVED->value,
                ];

                return in_array($deposit->status, $acceptedStatuses) ? $deposit->amount : 0;
            })->sum();

            $data['signup_code'] = $this->resource->signup_code;

            return $data;
        }

        return $data;
    }
}
