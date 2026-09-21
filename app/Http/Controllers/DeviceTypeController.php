<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceType;
use App\Models\AuditLog;

class DeviceTypeController extends Controller
{
    /**
     * Display a listing of device types and management interface
     */
    public function index()
    {
        $deviceTypes = DeviceType::withCount('assets')->orderBy('name')->get();
        $totalAssets = $deviceTypes->sum('assets_count');

        return view('device_types.index', compact('deviceTypes', 'totalAssets'));
    }

    /**
     * Store a newly created device type in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:it_device_types,name',
            'code' => 'nullable|string|max:50|unique:it_device_types,code',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }
        if (empty($validated['icon'])) {
            $validated['icon'] = 'bi-hdd-network';
        }

        $deviceType = DeviceType::create($validated);

        // Record Audit Log
        AuditLog::record(
            'create',
            'device_types',
            "เพิ่มประเภทอุปกรณ์ครุภัณฑ์ใหม่ '{$deviceType->name}'" . ($deviceType->code ? " (รหัส: {$deviceType->code})" : ""),
            $deviceType,
            null,
            $validated,
            $request,
            Auth::user()
        );

        return redirect()->route('device-types.index')->with('success', "เพิ่มประเภทอุปกรณ์ '{$deviceType->name}' เรียบร้อยแล้ว");
    }

    /**
     * Update the specified device type in storage
     */
    public function update(Request $request, DeviceType $deviceType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:it_device_types,name,' . $deviceType->id,
            'code' => 'nullable|string|max:50|unique:it_device_types,code,' . $deviceType->id,
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }
        if (empty($validated['icon'])) {
            $validated['icon'] = 'bi-hdd-network';
        }

        $oldValues = $deviceType->only(['name', 'code', 'icon', 'description']);
        $deviceType->update($validated);

        // Record Audit Log
        AuditLog::record(
            'update',
            'device_types',
            "แก้ไขข้อมูลประเภทอุปกรณ์ครุภัณฑ์ '{$deviceType->name}'",
            $deviceType,
            $oldValues,
            $validated,
            $request,
            Auth::user()
        );

        return redirect()->route('device-types.index')->with('success', "แก้ไขข้อมูลประเภทอุปกรณ์ '{$deviceType->name}' สำเร็จ");
    }

    /**
     * Remove the specified device type from storage
     */
    public function destroy(Request $request, DeviceType $deviceType)
    {
        $assetsCount = $deviceType->assets()->count();
        if ($assetsCount > 0) {
            return back()->with('error', "ไม่สามารถลบประเภทอุปกรณ์ '{$deviceType->name}' ได้ เนื่องจากมีครุภัณฑ์ในระบบใช้งานประเภทนี้อยู่ ({$assetsCount} เครื่อง)");
        }

        $name = $deviceType->name;
        $oldValues = $deviceType->toArray();

        // Record Audit Log
        AuditLog::record(
            'delete',
            'device_types',
            "ลบประเภทอุปกรณ์ครุภัณฑ์ '{$name}'",
            $deviceType,
            $oldValues,
            null,
            $request,
            Auth::user()
        );

        $deviceType->delete();

        return redirect()->route('device-types.index')->with('success', "ลบประเภทอุปกรณ์ '{$name}' เรียบร้อยแล้ว");
    }
}
