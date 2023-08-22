<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverController extends Controller
{
    // show
    public function show(Request $request)
    {
        // return back the user and associated driver
        $user = $request->user();
        $user->load('driver');
        return $user;
    }
    //update
    public function update(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric|between:2010,2023',
            'make' => 'required',
            'model' => 'required',
            'license_plate' => 'required',
            'color' => 'required',
            'name' => 'required',
        ]);

        $user = $request->user();

        $user->update($request->only('name'));

        $user->driver()->updateOrCreate($request->only(
            'year',
            'make',
            'model',
            'license_plate',
            'color'
        )
        );
        $user->load('driver');
        return $user;
    }
}