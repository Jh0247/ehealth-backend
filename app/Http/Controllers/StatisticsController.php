<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Blogpost;
use App\Models\Organization;
use App\Models\PurchaseRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * StatisticsController handles various statistical data retrievals.
 */
class StatisticsController extends Controller
{
    /**
     * Get the number of user registrations grouped by date.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userRegistrations(Request $request)
    {
        $registrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($registrations);
    }

    /**
     * Get the count of blogposts grouped by their status.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function blogpostStatus(Request $request)
    {
        $statuses = Blogpost::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($statuses);
    }

    /**
     * Get the count of appointments grouped by their type.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointmentsByType(Request $request)
    {
        $types = Appointment::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        return response()->json($types);
    }

    /**
     * Get the total sales over time grouped by date.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesOverTime(Request $request)
    {
        $sales = PurchaseRecord::select(DB::raw('DATE(date_purchase) as date'), DB::raw('sum(total_payment) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($sales);
    }

    /**
     * Get the total quantity of medications sold grouped by medication.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicationsSold(Request $request)
    {
        $medications = PurchaseRecord::select('medication_id', DB::raw('sum(quantity) as total_sold'))
            ->groupBy('medication_id')
            ->with('medication')
            ->get();

        $medications = $medications->map(function ($record) {
            return [
                'medication_name' => $record->medication->name,
                'total_sold' => $record->total_sold,
            ];
        });

        return response()->json($medications);
    }

    /**
     * Get the statistics for a specific organization.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function organizationStats($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $numStaffs = $organization->users()->count();
        $numAppointments = $organization->appointments()->count();
        $numBlogposts = $organization->users()->withCount('blogposts')->get()->sum('blogposts_count');

        return response()->json([
            'organization' => $organization,
            'stats' => [
                'num_staffs' => $numStaffs,
                'num_appointments' => $numAppointments,
                'num_blogposts' => $numBlogposts
            ]
        ]);
    }
}