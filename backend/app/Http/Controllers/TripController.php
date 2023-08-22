<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripCreated;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    //store empty method
    public function store(Request $request)
    {
        //validate
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required',
        ]);
        //create trip
        $trip = $request->user()->trips()->create(
            $request->only(
                'origin',
                'destination',
                'destination_name'
            )
        );

        TripCreated::dispatch($trip, $request->user());

        //return trip
        return $trip;
    }
    public function show(Request $request, Trip $trip)
    {
        if ($trip->user->id === $request->user()->id) {
            # code...
            return $trip;
        }
        if ($trip->driver() && $request->user()->driver) {
            if ($trip->driver->id === $request->user()->driver->id) {
                return $trip;
            }
            # code...
        }
        return response()->json(['Message' => 'Cannot find this trip'], 404);
    }
    public function accept(Request $request, Trip $trip)
    {
        // driver accepts trip

        $request->validate([
            'driver_location' => 'required',
        ]);
        $trip->update([
            'driver_id' => $request->user()->driver->id,
            'driver_location' => $request->driver_location,
        ]);
        $trip->load('driver.user');
        TripAccepted::dispatch($trip, $trip->user);
        return $trip;

    }
    public function start(Request $request, Trip $trip)
    {

        // driver starts trip
        $trip->update([
            'is_started' => true,
        ]);
        $trip->load('driver.user');
        TripStarted::dispatch($trip, $request->user());
        return $trip;
    }
    public function end(Request $request, Trip $trip)
    {
        // driver ends trip
        $trip->update([
            'is_completed' => true,
        ]);
        $trip->load('driver.user');
        TripEnded::dispatch($trip, $request->user());
        return $trip;
    }
    public function location(Request $request, Trip $trip)
    {
        // driver updates location
        $request->validate([
            'driver_location' => 'required',
        ]);
        $trip->update([

            'driver_location' => $request->driver_location,
        ]);
        $trip->load('driver.user');
        TripLocationUpdated::dispatch($trip, $trip->user);
        return $trip;
    }
}