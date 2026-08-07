<?php

namespace App\Http\Controllers;

use App\Actions\Message\CreateMessageAction;
use App\Actions\Message\NotifyMessageRecipientsAction;
use App\Actions\Message\UpdateMessageAction;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Jobs\SendMessageNotificationJob;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        Gate::authorize('viewAny', Message::class);

        $user = Auth::user();

        if ($user->isParent()) {
            $messages = Message::forRecipient($user)->get();
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
        Gate::authorize('create', Message::class);

        $message = $action->exectute($request->validated());

        NotifyMessageRecipientsAction::execute($message, $message->recipients);

        SendMessageNotificationJob::dispatch($message->id);

        return new MessageResource($message);

    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message): MessageResource
    {

        Gate::authorize('view', $message);

        $message->markAsReadFor(Auth::user());

        return new MessageResource($message);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessageRequest $request, Message $message, UpdateMessageAction $action)
    {

        Gate::authorize('update', $message);

        $newRecipients = $action->execute($message, $request->validated());

        NotifyMessageRecipientsAction::execute($message, $newRecipients);

        SendMessageNotificationJob::dispatch($message->id);

        return new MessageResource($message->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        Gate::authorize('delete', $message);

        $message->delete();

        return response()->noContent();
    }
}
