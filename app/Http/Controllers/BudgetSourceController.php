<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetSource;
use App\Models\AuditLog;

class BudgetSourceController extends Controller
{
    public function index()
    {
        $budgetSources = BudgetSource::withCount('assets')->orderBy('name')->get();
        $totalAssets = $budgetSources->sum('assets_count');

        return view('budget_sources.index', compact('budgetSources', 'totalAssets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:it_budget_sources,name',
            'code' => 'nullable|string|max:50|unique:it_budget_sources,code',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['color'] = $validated['color'] ?? '#0284c7';

        $budgetSource = BudgetSource::create($validated);

        AuditLog::record(
            'create',
            'budget_sources',
            "เพิ่มแหล่งเงินงบประมาณ '{$budgetSource->name}'" . ($budgetSource->code ? " (รหัส: {$budgetSource->code})" : ""),
            $budgetSource,
            null,
            $validated,
            $request,
            Auth::user()
        );

        return redirect()->route('budget-sources.index')->with('success', "เพิ่มแหล่งเงิน '{$budgetSource->name}' เรียบร้อยแล้ว");
    }

    public function update(Request $request, BudgetSource $budgetSource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:it_budget_sources,name,' . $budgetSource->id,
            'code' => 'nullable|string|max:50|unique:it_budget_sources,code,' . $budgetSource->id,
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldData = $budgetSource->only(['name', 'code', 'color', 'description', 'is_active']);
        $budgetSource->update($validated);

        AuditLog::record(
            'update',
            'budget_sources',
            "แก้ไขแหล่งเงินงบประมาณ '{$budgetSource->name}'",
            $budgetSource,
            $oldData,
            $validated,
            $request,
            Auth::user()
        );

        return redirect()->route('budget-sources.index')->with('success', "อัปเดตแหล่งเงิน '{$budgetSource->name}' เรียบร้อยแล้ว");
    }

    public function destroy(BudgetSource $budgetSource)
    {
        if ($budgetSource->assets()->count() > 0) {
            return back()->with('error', "ไม่สามารถลบแหล่งเงิน '{$budgetSource->name}' ได้ เนื่องจากมีครุภัณฑ์ในระบบอ้างอิงอยู่ {$budgetSource->assets()->count()} รายการ");
        }

        $name = $budgetSource->name;
        $budgetSource->delete();

        AuditLog::record(
            'delete',
            'budget_sources',
            "ลบแหล่งเงินงบประมาณ '{$name}'",
            null,
            null,
            ['name' => $name],
            request(),
            Auth::user()
        );

        return redirect()->route('budget-sources.index')->with('success', "ลบแหล่งเงิน '{$name}' เรียบร้อยแล้ว");
    }
}
