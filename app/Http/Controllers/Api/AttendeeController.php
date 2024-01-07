<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    use CanLoadRelationships;
    public function __construct(){
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->authorizeResource(Attendee::class, 'event');

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $attendees = $event->attendees()->latest();
        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $attendee = $event->attendees()->create([
            'user_id' => 1
        ]);
        return new AttendeeResource($attendee);
    }

    public function shouldIncludeRelation(string $relation)
    {
        //get query params 
        $include = request()->query('include');
        if (!$include) {
            return false;
        }

        $relations = array_map('trim',explode(',', $include));
        return in_array($relation, $relations);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource(
            $this->loadRelationships($attendee)
        );
    } 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event ,Attendee $attendee)
    {
        $this->authorize('delete-attendee',[$event,$attendee]);
        $attendee->delete();
        return response()->json([
            'message'=>'Attendee successfully deleted'
        ]);
        //
    }
}
