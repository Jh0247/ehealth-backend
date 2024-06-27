<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Blogpost;
use App\Models\Organization;
use App\Models\PurchaseRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function userRegistrations(Request $request)
    {
        $registrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($registrations);
    }

    public function blogpostStatus(Request $request)
    {
        $statuses = Blogpost::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($statuses);
    }

    public function appointmentsByType(Request $request)
    {
        $types = Appointment::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        return response()->json($types);
    }

    public function salesOverTime(Request $request)
    {
        $sales = PurchaseRecord::select(DB::raw('DATE(date_purchase) as date'), DB::raw('sum(total_payment) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($sales);
    }

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

    public function organizationStats($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        // Number of staff in the organization
        $numStaffs = $organization->users()->count();

        // Number of appointments in the organization
        $numAppointments = $organization->appointments()->count();

        // Number of blog posts created by users in the organization
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
