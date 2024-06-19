<?php

declare(strict_types=1);

namespace App\Http\Resources\Withdrawal;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Withdrawal */
class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        $data = [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'requisites' => $this->requisites,
            'status' => $this->status,
            'method' => $this->method,
            'currency' => $this->currency,
            'user_id' => $this->user_id,
            'created_at' => (int)$this->resource->created_at->timestamp,
            'updated_at' => (int)$this->resource->updated_at->timestamp,
            'user' => $this->resource->user_id ? $this->resource->user : null,
        ];

        if (!$user->hasRole('user')) {
            $data['admin_notes'] = $this->admin_notes;

            return $data;
        }

        return $data;
    }
}
