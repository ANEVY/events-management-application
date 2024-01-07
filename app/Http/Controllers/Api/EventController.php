<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use CanLoadRelationships;
    private array $relations = ['user','attendees','attendees.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->authorizeResource(Event::class, 'event');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {        
        $query = $this->loadRelationships( Event::query());
        // return EventResource::collection( Event::with('user')->with('attendees')->get() );
        return EventResource::collection( $query->latest()->paginate() );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate request
       $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'required|date',
            'end_time'  => 'required|date|after:start_time',
        ]);

       $event = Event::create([
            ...$data,
            'user_id' => $request->user()->id
        ]);
        return $event;
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user','attendees');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // if( Gate::denies('update-event', $event) ){
        //     abort(403,'You are not authorized to modify this event');
        // }
        $this->authorize('update-event',$event);
        $event->update($request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'sometimes|date',
            'end_time'  => 'sometimes|date|after:start_time',
        ]));
        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        // return response()->json(["message"=>"Event has been deleted"]);
        return response(status: 204);
    }
}
