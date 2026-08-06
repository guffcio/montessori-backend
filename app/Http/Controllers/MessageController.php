<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Jobs\SendMessageNotificationJob;
use App\Models\Message;
use CreateMessageAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isParent()) {
            $messages = Message::query()->whereHas('recipients', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();
        } else {
            $messages = Message::all();
        }

        return MessageResource::collection($messages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessageRequest $request, CreateMessageAction $action)
    {
        $message = $action->exectute($request->validated());

        SendMessageNotificationJob::dispatch($message->id);

        return new MessageResource($message);

    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message): MessageResource
    {
        return new MessageResource($message);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        $message->delete();

        return response()->noContent();
    }
}
