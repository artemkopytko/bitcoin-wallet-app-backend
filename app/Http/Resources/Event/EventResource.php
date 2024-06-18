<?php

declare(strict_types=1);

namespace App\Http\Resources\Event;

use App\Enums\Roles;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\Wallet\WalletResource;
use App\Models\Deposit;
use App\Models\Event;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /** @var Event */
    public $resource;

    public function __construct(Event $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id ?? null,
            'action' => $this->resource->action,
            'description' => $this->resource->description,
            'created_at' => (int)$this->resource->created_at->timestamp,
            'updated_at' => (int)$this->resource->updated_at->timestamp,
        ];

        return $data;
    }
}
