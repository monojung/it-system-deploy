<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SparePart;
use App\Models\AuditLog;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePart::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereRaw('stock_quantity <= minimum_quantity');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('part_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $parts = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = SparePart::select('category')->whereNotNull('category')->distinct()->pluck('category');
        $lowStockCount = SparePart::whereRaw('stock_quantity <= minimum_quantity')->count();
        $totalItemsCount = SparePart::count();

        return view('spare_parts.index', compact('parts', 'categories', 'lowStockCount', 'totalItemsCount'));
    }

    public function create()
    {
        return view('spare_parts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_code' => 'required|string|max:50|unique:it_spare_parts,part_code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $part = SparePart::create($request->all());

        return redirect()->route('spare-parts.index')->with('success', "เพิ่มรายการพัสดุ '{$part->name}' เรียบร้อยแล้ว");
    }

    public function edit(SparePart $sparePart)
    {
        return view('spare_parts.edit', compact('sparePart'));
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $request->validate([
            'part_code' => 'required|string|max:50|unique:it_spare_parts,part_code,' . $sparePart->id,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $sparePart->update($request->all());

        return redirect()->route('spare-parts.index')->with('success', "แก้ไขข้อมูลพัสดุ '{$sparePart->name}' เรียบร้อยแล้ว");
    }

    public function adjustStock(Request $request, SparePart $sparePart)
    {
        $request->validate([
            'action_type' => 'required|in:add,set',
            'quantity' => 'required|integer',
        ]);

        $oldQty = $sparePart->stock_quantity;
        if ($request->action_type === 'add') {
            $sparePart->increment('stock_quantity', $request->quantity);
            $sparePart->refresh();
            $msg = "รับเข้าพัสดุ '{$sparePart->name}' จำนวน {$request->quantity} {$sparePart->unit} สำเร็จ (ยอดคงเหลือใหม่: {$sparePart->stock_quantity} {$sparePart->unit})";
        } else {
            $sparePart->update(['stock_quantity' => max(0, $request->quantity)]);
            $msg = "ปรับปรุงสต็อก '{$sparePart->name}' เป็น {$sparePart->stock_quantity} {$sparePart->unit} เรียบร้อยแล้ว";
        }

        AuditLog::record('stock_adjust', 'spare_parts', $msg, $sparePart, ['stock_quantity' => $oldQty], ['stock_quantity' => $sparePart->stock_quantity]);

        return back()->with('success', $msg);
    }

    public function destroy(SparePart $sparePart)
    {
        if ($sparePart->repairParts()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบพัสดุนี้ได้ เนื่องจากมีประวัติการเบิกใช้ในใบงานซ่อม');
        }

        $name = $sparePart->name;
        $sparePart->delete();

        return redirect()->route('spare-parts.index')->with('success', "ลบพัสดุ '{$name}' เรียบร้อยแล้ว");
    }
}
