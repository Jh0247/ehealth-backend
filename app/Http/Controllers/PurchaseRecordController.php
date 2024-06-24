<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRecord;
use Illuminate\Http\Request;

class PurchaseRecordController extends Controller
{
    public function getUserPurchaseRecords(Request $request)
    {
        $user = $request->user();
        $purchases = PurchaseRecord::where('user_id', $user->id)->with('medication')->get();

        return response()->json($purchases);
    }
}
