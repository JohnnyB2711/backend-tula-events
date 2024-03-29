<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventsUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getProfile()
    {
        return $user = Auth::user();
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'email' => 'email|unique:users,email,' . $user->id,
            'name' => 'required|string',
            'surname' => 'required|string',
            'phone' => 'required|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|string',
            'name_organization' => 'required|string'
        ]);
        foreach ($request->only([
            'email',
            'password',
            'name',
            'surname',
            'phone',
            'avatar',
            'name_organization'
        ]) as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();
        return $user;
    }

    public function getEvents(Request $request)
    {
        $now = Carbon::now()->toDateTimeString();
        $user = Auth::user();
        if ($user->hasAnyRole('participant')) {
            $events = $user->participantEvents($request->past , $now);
        }
        if ($user->hasAnyRole('organizer')) {
            $events = $user->organizerEvents($request->past, $now);
        }
        //проверить на null
        return $events->simplePaginate(20);
    }
}
