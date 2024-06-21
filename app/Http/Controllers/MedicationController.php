<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{
    public function getUserMedications(Request $request)
    {
        $user = $request->user;
        $user = User::find($user->id)->load('purchaseRecordsAsUser');

        return response()->json($user->purchaseRecordsAsUser);
    }

    public function getAllMedications()
    {
        $medications = Medication::all();
        return response()->json($medications);
    }
}
