<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function getUserAppointments(Request $request)
    {
        $user = $request->user;
        $user = User::find($user->id)->load('appointmentsAsUser');

        return response()->json([
            'appointments' => $user->appointmentsAsUser,
        ]);
    }
}
