<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ServiceController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $services = Service::all();

        $settings = SystemSetting::whereIn('SETTING_KEY', [
            'service_fee', 'delivery_fee', 'driver_service_fee_share', 'driver_delivery_fee_share'
        ])->pluck('SETTING_VALUE', 'SETTING_KEY');

        $serviceFee = $settings['service_fee'] ?? '2.00';
        $deliveryFee = $settings['delivery_fee'] ?? '6.00';
        $driverServiceShare = $settings['driver_service_fee_share'] ?? '20.00';
        $driverDeliveryShare = $settings['driver_delivery_fee_share'] ?? '100.00';

        return view('admin.services', compact('services', 'serviceFee', 'deliveryFee', 'driverServiceShare', 'driverDeliveryShare'));
    }

    public function updateSettings(Request $request)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'service_fee' => 'required|numeric|min:0',
            'delivery_fee' => 'required|numeric|min:0',
            'driver_service_fee_share' => 'required|numeric|min:0|max:100',
            'driver_delivery_fee_share' => 'required|numeric|min:0|max:100',
        ]);

        SystemSetting::updateOrCreate(['SETTING_KEY' => 'service_fee'], ['SETTING_VALUE' => $request->service_fee]);
        SystemSetting::updateOrCreate(['SETTING_KEY' => 'delivery_fee'], ['SETTING_VALUE' => $request->delivery_fee]);
        SystemSetting::updateOrCreate(['SETTING_KEY' => 'driver_service_fee_share'], ['SETTING_VALUE' => $request->driver_service_fee_share]);
        SystemSetting::updateOrCreate(['SETTING_KEY' => 'driver_delivery_fee_share'], ['SETTING_VALUE' => $request->driver_delivery_fee_share]);

        return back()->with('status', 'Pricing settings saved successfully.');
    }
}