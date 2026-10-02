<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcquisitionMethod;
use App\Models\AuditLog;

class AcquisitionMethodController extends Controller
{
    public function index()
    {
        $acquisitionMethods = AcquisitionMethod::withCount('assets')->orderBy('name')->get();
        $totalAssets = $acquisitionMethods->sum('assets_count');

        return view('acquisition_methods.index', compact('acquisitionMethods', 'totalAssets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:it_acquisition_methods,name',
            'code' => 'nullable|string|max:50|unique:it_acquisition_methods,code',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['color'] = $validated['color'] ?? '#0d9488';

        $method = AcquisitionMethod::create($validated);

        AuditLog::record(
            'create',
            'acquisition_methods',
            "เพิ่มวิธีการได้มาของครุภัณฑ์ '{$method->name}'" . ($method->code ? " (รหัส: {$method->code})" : ""),
            $method,
            null,
            $validated,
            $request,
            Auth::user()
        );

        return redirect()->route('acquisition-methods.index')->with('success', "เพิ่มวิธีการได้มา '{$method->name}' เรียบร้อยแล้ว");
    }

    public function update(Request $request, AcquisitionMethod $acquisitionMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:it_acquisition_methods,name,' . $acquisitionMethod->id,
            'code' => 'nullable|string|max:50|unique:it_acquisition_methods,code,' . $acquisitionMethod->id,
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldData = $acquisitionMethod->only(['name', 'code', 'color', 'description', 'is_active']);
        $acquisitionMethod->update($validated);

        AuditLog::record(
            'update',
            'acquisition_methods',
            "แก้ไขวิธีการได้มาของครุภัณฑ์ '{$acquisitionMethod->name}'",
            $acquisitionMethod,
            $oldData,
            $validated,
            $request,
            Auth::user()
        );

        return redirect()->route('acquisition-methods.index')->with('success', "อัปเดตวิธีการได้มา '{$acquisitionMethod->name}' เรียบร้อยแล้ว");
    }

    public function destroy(AcquisitionMethod $acquisitionMethod)
    {
        if ($acquisitionMethod->assets()->count() > 0) {
            return back()->with('error', "ไม่สามารถลบวิธีการได้มา '{$acquisitionMethod->name}' ได้ เนื่องจากมีครุภัณฑ์ในระบบอ้างอิงอยู่ {$acquisitionMethod->assets()->count()} รายการ");
        }

        $name = $acquisitionMethod->name;
        $acquisitionMethod->delete();

        AuditLog::record(
            'delete',
            'acquisition_methods',
            "ลบวิธีการได้มาของครุภัณฑ์ '{$name}'",
            null,
            null,
            ['name' => $name],
            request(),
            Auth::user()
        );

        return redirect()->route('acquisition-methods.index')->with('success', "ลบวิธีการได้มา '{$name}' เรียบร้อยแล้ว");
    }
}
