<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadStatus;
use App\Models\LeadSource;
use App\Models\User;

class LeadFieldController extends Controller
{
    /**
     * Get all lookup metadata for lead forms and filters.
     */
    public function index()
    {
        return response()->json([
            'statuses' => LeadStatus::orderBy('order_num', 'asc')->get(),
            'sources' => LeadSource::get(),
            'staff' => User::select('id', 'name', 'email')->get(),
        ]);
    }
}
