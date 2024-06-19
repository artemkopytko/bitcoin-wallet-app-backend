<?php

declare(strict_types=1);

namespace App\Http\Resources\Deposit;

use App\Enums\Roles;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\Wallet\WalletResource;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
{
    /** @var Deposit */
    public $resource;

    public function __construct(Deposit $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id ?? null,
            'wallet_id' => $this->resource->wallet_id ?? null,
            'staff_id' => $this->resource->staff_id ?? null,
            'amount' => (float) $this->resource->amount,
            'status' => $this->resource->status,
            'created_at' => (int)$this->resource->created_at->timestamp,
            'updated_at' => (int)$this->resource->updated_at->timestamp,
            'user' => $this->resource->user_id ? $this->resource->user : null,
            'avatar_url' => $this->resource->user_id ? $this->resource->user->getAvatarUrl() : null,
        ];

        // Protect sensitive data from user access
        if (!$user->hasRole(Roles::USER)) {
            $data['note'] = $this->resource->note;
            $data['staff'] = $this->resource->staff_id ? $this->resource->staff : null;
            $data['wallet'] = $this->resource->wallet_id ? $this->resource->wallet : null;
        }

        return $data;
    }
}
