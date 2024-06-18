<?php

namespace App\Http\Controllers;


use App\Http\Requests\Event\EventReadRequest;
use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\Event\EventResource;
use App\Http\Responses\SuccessResponse;
use App\Models\Event;
use App\Models\Deposit;

class EventController extends Controller
{
    public function browse(EventReadRequest $request): EventCollection
    {
        /** @var Event $items */
        $items = Event::query()
            ->orderBy($request->input('sort_by') ?? 'id',
                $request->input('sort_order') ?? 'desc');

        if ($request->has('user_id')) {
            $items->where('user_id', $request->input('user_id'));
        }

        return new EventCollection(
            $items->paginate(
                $request->input('per_page', 25)
            )
        );
    }

    public function read(EventReadRequest $request, Event $item): SuccessResponse
    {
        return new SuccessResponse(new EventResource($item));
    }
}
