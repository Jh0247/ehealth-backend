<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRecord;
use Illuminate\Http\Request;

/**
 * PurchaseRecordController handles all operations related to user purchase records.
 */
class PurchaseRecordController extends Controller
{
    /**
     * Get the purchase records for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserPurchaseRecords(Request $request)
    {
        $user = $request->user();
        $purchases = PurchaseRecord::where('user_id', $user->id)->with('medication')->get();

        return response()->json($purchases);
    }
}
