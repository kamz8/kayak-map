<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RiverTrack;
use Illuminate\Http\Request;

class RiverTrackController extends Controller
{
    public function show(Request $request)
    {
        $riverTrack = RiverTrack::find($request->id); // Pobierz przykładową trasę rzeki
        return response()->json($riverTrack);
    }
}
