<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IpsCalculatorController extends Controller
{
    public function index()
{
    return view('ips.form');
}

    public function calculate(Request $request)
    {
        $request->validate([
            'load' => 'required|numeric',
            'backup_time' => 'required|numeric',
            'voltage' => 'required|numeric',
        ]);

        $load = $request->load;
        $time = $request->backup_time;
        $voltage = $request->voltage;

        $efficiency = 0.85;

        // Step 1: মোট Energy
        $energy = $load * $time;

        // Step 2: Loss সহ Energy
        $required_energy = $energy / $efficiency;

        // Step 3: Battery Ah
        $ah = $required_energy / $voltage;

        // Step 4: Safe Ah (70%)
        $safe_ah = $ah / 0.7;

        // Step 5: IPS VA
        $va = $load / 0.8;

        return view('ips.form', compact('ah', 'safe_ah', 'va'));
    }

    // public function calculate(Request $request)
    // {
    //     $request->validate([
    //         'load' => 'required',
    //         'backup_time' => 'required',
    //         'voltage' => 'required'
    //     ]);

    //     $devices = json_decode($request->devices_data, true);

    //     $load = $request->load;
    //     $time = $request->backup_time;
    //     $voltage = $request->voltage;

    //     $efficiency = 0.85;

    //     $energy = $load * $time;
    //     $required_energy = $energy / $efficiency;
    //     $ah = $required_energy / $voltage;
    //     $safe_ah = $ah / 0.7;
    //     $va = $load / 0.8;

    //     return view('ips.result', compact(
    //         'ah','safe_ah','va','devices','load','time','voltage'
    //     ));
    // }
}
