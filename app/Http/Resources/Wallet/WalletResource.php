<?php

declare(strict_types=1);

namespace App\Http\Resources\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /** @var Wallet */
    public $resource;

    public function __construct(Wallet $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'address' => $this->resource->address,
            'type' => $this->resource->type,
            'is_active' => (bool) $this->resource->is_active,
            'created_at' => (int) $this->resource->created_at->timestamp,
            'updated_at' => (int) $this->resource->updated_at->timestamp,
        ];

        if (!$user->hasRole('user')) {
            $data['deposit_sum'] = $this->resource->sumOfSuccessDeposits();

            return $data;
        }

        return $data;
    }
}
