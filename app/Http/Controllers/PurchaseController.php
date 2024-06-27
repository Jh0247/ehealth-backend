<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRecord;
use App\Models\Organization;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\PurchaseRecordCreationValidationStrategy;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * PurchaseController handles all operations related to purchase records.
 */
class PurchaseController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $purchaseValidatorContext;

    /**
     * PurchaseController constructor.
     */
    public function __construct()
    {
        $this->purchaseValidatorContext = new ValidatorContext();
        $this->purchaseValidatorContext->addStrategy(new PurchaseRecordCreationValidationStrategy());
    }

    /**
     * Get all purchase records that are made from the same organization.
     *
     * @param int $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Create a new purchase record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPurchaseRecord(Request $request)
    {
        $validationResult = $this->purchaseValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors'], 422);
        }

        $purchase = PurchaseRecord::create($request->all());

        return response()->json([
            'message' => 'Purchase record created successfully',
            'purchase' => $purchase
        ], 201);
    }

    /**
     * Delete a specific purchase record by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePurchaseRecord($id)
    {
        $purchase = PurchaseRecord::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase record not found'], 404);
        }

        $purchase->delete();

        return response()->json(['message' => 'Purchase record deleted successfully'], 200);
    }

    /**
     * Get purchase statistics for a specific organization.
     *
     * @param int $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseStatistics($organizationId)
    {
        $organization = Organization::find($organizationId);
    
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }
    
        $totalPurchases = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->count();
    
        $totalSales = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->sum('total_payment');
    
        $todaySales = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->whereDate('date_purchase', Carbon::today())->sum('total_payment');
    
        $startDate = Carbon::now()->subMonth();
        $dailySales = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->whereBetween('date_purchase', [$startDate, Carbon::now()])
            ->selectRaw('DATE(date_purchase) as date, SUM(total_payment) as total_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    
        $salesByMedication = PurchaseRecord::whereHas('pharmacist', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->selectRaw('medication_id, SUM(total_payment) as total_sales')
            ->groupBy('medication_id')
            ->with('medication')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'medication_name' => $record->medication->name,
                    'total_sales' => $record->total_sales,
                ];
            });
    
        return response()->json([
            'total_purchases' => $totalPurchases,
            'total_sales' => $totalSales,
            'today_sales' => $todaySales,
            'daily_sales' => $dailySales,
            'sales_by_medication' => $salesByMedication,
        ]);
    }
}