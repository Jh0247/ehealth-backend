<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRecord;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    // Get all purchase records that are made from the same organization
    public function getAllPurchasesByOrganization($organizationId)
    {
        $purchases = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->with(['user', 'medication'])->get();
    
        if ($purchases->isEmpty()) {
            return response()->json(['message' => 'No purchases found for this organization'], 404);
        }
    
        return response()->json($purchases);
    }

    // Create purchase record
    public function createPurchaseRecord(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pharmacist_id' => 'required|exists:users,id',
            'medication_id' => 'required|exists:medications,id',
            'date_purchase' => 'required|date',
            'quantity' => 'required|integer',
            'total_payment' => 'required|numeric',
        ]);

        $purchase = PurchaseRecord::create($request->all());

        return response()->json([
            'message' => 'Purchase record created successfully',
            'purchase' => $purchase
        ], 201);
    }

    // Delete purchase record
    public function deletePurchaseRecord($id)
    {
        $purchase = PurchaseRecord::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase record not found'], 404);
        }

        $purchase->delete();

        return response()->json(['message' => 'Purchase record deleted successfully'], 200);
    }

    // Get statistic data for purchase records
    public function getPurchaseStatistics($organizationId)
    {
        $organization = Organization::find($organizationId);

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        // Total purchase records made
        $totalPurchases = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->count();

        // Total sales made by organization
        $totalSales = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->sum('total_payment');

        // Total sales made today by organization
        $todaySales = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->whereDate('date_purchase', Carbon::today())->sum('total_payment');

        return response()->json([
            'total_purchases' => $totalPurchases,
            'total_sales' => $totalSales,
            'today_sales' => $todaySales,
        ]);
    }
}
