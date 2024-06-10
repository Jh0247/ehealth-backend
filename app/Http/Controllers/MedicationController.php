<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{
    public function getUserMedications(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }
        
        $user = User::find($user->id)->load('purchaseRecordsAsUser');

        return response()->json([
            'medications' => $user->purchaseRecordsAsUser,
        ]);
    }
}
