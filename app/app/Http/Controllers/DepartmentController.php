<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['users', 'assets', 'repairs'])->orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:it_departments,name',
            'code' => 'nullable|string|max:50',
            'building' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Department::create($request->all());

        return redirect()->route('departments.index')->with('success', "เพิ่มแผนก '{$request->name}' สำเร็จ");
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:it_departments,name,' . $department->id,
            'code' => 'nullable|string|max:50',
            'building' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $department->update($request->all());

        return redirect()->route('departments.index')->with('success', "แก้ไขข้อมูลแผนก '{$department->name}' สำเร็จ");
    }

    public function destroy(Department $department)
    {
        if ($department->assets()->count() > 0 || $department->repairs()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบแผนกนี้ได้ เนื่องจากมีครุภัณฑ์หรือประวัติงานซ่อมผูกอยู่');
        }

        $name = $department->name;
        $department->delete();

        return redirect()->route('departments.index')->with('success', "ลบแผนก '{$name}' สำเร็จ");
    }
}
