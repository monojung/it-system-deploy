<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DataRequest;
use App\Models\Department;
use App\Models\AuditLog;

class DataRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DataRequest::with(['user', 'department', 'handler'])->orderBy('id', 'desc');

        // Regular users can only see their own requests
        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('request_no', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('criteria_detail', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }

        $requests = $query->paginate($perPage)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => $user->isUser() ? DataRequest::where('user_id', $user->id)->count() : DataRequest::count(),
            'pending' => $user->isUser() ? DataRequest::where('user_id', $user->id)->where('status', 'pending')->count() : DataRequest::where('status', 'pending')->count(),
            'in_progress' => $user->isUser() ? DataRequest::where('user_id', $user->id)->whereIn('status', ['approved', 'in_progress'])->count() : DataRequest::whereIn('status', ['approved', 'in_progress'])->count(),
            'completed' => $user->isUser() ? DataRequest::where('user_id', $user->id)->where('status', 'completed')->count() : DataRequest::where('status', 'completed')->count(),
        ];

        return view('data_requests.index', compact('requests', 'departments', 'stats'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $cloneRequest = null;

        if ($request->filled('clone_id')) {
            $existing = DataRequest::find($request->clone_id);
            if ($existing && (!$user->isUser() || $existing->user_id === $user->id || $user->isAdmin() || $user->isTechnician())) {
                $cloneRequest = $existing;
            }
        }

        return view('data_requests.create', compact('departments', 'user', 'cloneRequest'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'objective_type' => 'required|string|in:ha_quality,research,executive,external,other',
            'objective_detail' => 'nullable|string|max:1000',
            'data_start_date' => 'nullable|date',
            'data_end_date' => 'nullable|date|after_or_equal:data_start_date',
            'criteria_detail' => 'required|string|max:3000',
            'file_format' => 'required|string|in:excel,csv,pdf,text',
            'urgency' => 'required|string|in:normal,urgent,very_urgent',
            'pdpa_consent' => 'accepted',
            'department_id' => 'nullable|exists:it_departments,id',
            'sample_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,xls,xlsx,csv,doc,docx,zip|max:10240',
            're_request_from_id' => 'nullable|exists:it_data_requests,id',
        ]);

        $user = Auth::user();
        $reqNo = DataRequest::generateRequestNo();

        $samplePath = null;
        $sampleName = null;
        if ($request->hasFile('sample_file')) {
            $file = $request->file('sample_file');
            $sampleName = $file->getClientOriginalName();
            $filename = 'sample_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/data_requests_samples';
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0777, true);
            }
            $file->move(public_path($path), $filename);
            $samplePath = $path . '/' . $filename;
        }

        $dataRequest = DataRequest::create([
            'request_no' => $reqNo,
            'user_id' => $user->id,
            'department_id' => $request->department_id ?: $user->department_id,
            'title' => $request->title,
            'objective_type' => $request->objective_type,
            'objective_detail' => $request->objective_detail,
            'data_start_date' => $request->data_start_date,
            'data_end_date' => $request->data_end_date,
            'criteria_detail' => $request->criteria_detail,
            'sample_file' => $samplePath,
            'sample_filename' => $sampleName,
            'file_format' => $request->file_format,
            'urgency' => $request->urgency,
            're_request_from_id' => $request->re_request_from_id,
            'pdpa_consent' => true,
            'status' => 'pending',
        ]);

        return redirect()->route('data-requests.show', $dataRequest->id)
            ->with('success', "ยื่นคำขอข้อมูลสารสนเทศรหัส {$reqNo} เรียบร้อยแล้ว กลุ่มงานสุขภาพดิจิทัลจะดำเนินการตรวจสอบต่อไป");
    }

    public function show($id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::with(['user', 'department', 'handler', 'reRequestFrom'])->findOrFail($id);

        // Access check
        if ($user->isUser() && $dataRequest->user_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงคำขอข้อมูลสารสนเทศนี้');
        }

        $sqlPresets = [
            [
                'name' => '1. 10 อันดับโรคผู้ป่วยนอก (Top 10 OPD Diagnoses)',
                'description' => 'จัดอันดับโรคที่มารับบริการมากที่สุด พร้อมชื่อโรคภาษาไทย',
                'sql' => "SELECT ov.pdx AS icd10, icd.name AS icd10_name, COUNT(ov.vn) AS total_visit, COUNT(DISTINCT ov.hn) AS total_patient\nFROM ovst ov\nLEFT JOIN icd101 icd ON ov.pdx = icd.code\nWHERE ov.vstdate BETWEEN ':start_date' AND ':end_date'\n  AND ov.pdx IS NOT NULL AND ov.pdx != ''\nGROUP BY ov.pdx, icd.name\nORDER BY total_visit DESC\nLIMIT 10;",
            ],
            [
                'name' => '2. ผู้ป่วยคลินิกพิเศษ NCD เบาหวาน/ความดัน (DM & HT)',
                'description' => 'รายชื่อและข้อมูลตรวจวัดผู้ป่วยคลินิกเบาหวานและความดันโลหิตสูง',
                'sql' => "SELECT ov.vstdate, ov.hn, pt.pname, pt.fname, pt.lname, pt.cid, TIMESTAMPDIFF(YEAR, pt.birthday, ov.vstdate) AS age_y,\n       ov.pdx, ptt.name AS pttype_name, op.bps, op.bpd, op.bw\nFROM ovst ov\nINNER JOIN patient pt ON ov.hn = pt.hn\nLEFT JOIN pttype ptt ON ov.pttype = ptt.pttype\nLEFT JOIN opdscreen op ON ov.vn = op.vn\nWHERE ov.vstdate BETWEEN ':start_date' AND ':end_date'\n  AND (ov.pdx LIKE 'E10%' OR ov.pdx LIKE 'E11%' OR ov.pdx LIKE 'E14%' OR ov.pdx LIKE 'I10%')\nORDER BY ov.vstdate DESC\nLIMIT 500;",
            ],
            [
                'name' => '3. สรุปผู้ป่วยนอกจำแนกตามสิทธิการรักษา (Visits by PTTYPE)',
                'description' => 'สรุปจำนวนครั้งและจำนวนคนแยกตามสิทธิ เช่น บัตรทอง, ประกันสังคม, ข้าราชการ',
                'sql' => "SELECT ptt.pttype, ptt.name AS pttype_name, COUNT(ov.vn) AS total_visits, COUNT(DISTINCT ov.hn) AS total_patients\nFROM ovst ov\nLEFT JOIN pttype ptt ON ov.pttype = ptt.pttype\nWHERE ov.vstdate BETWEEN ':start_date' AND ':end_date'\nGROUP BY ptt.pttype, ptt.name\nORDER BY total_visits DESC;",
            ],
            [
                'name' => '4. ข้อมูลการส่งต่อผู้ป่วยนอก (OPD Refer Out)',
                'description' => 'สถิติการส่งต่อผู้ป่วยไปรับการรักษาต่อยังโรงพยาบาลแม่ข่าย',
                'sql' => "SELECT ro.refer_date, ro.hn, pt.fname, pt.lname, ro.pdx, ro.refer_hospcode, hosp.name AS hosp_name, ro.refer_cause\nFROM referout ro\nINNER JOIN patient pt ON ro.hn = pt.hn\nLEFT JOIN hospcode hosp ON ro.refer_hospcode = hosp.hospcode\nWHERE ro.refer_date BETWEEN ':start_date' AND ':end_date'\nORDER BY ro.refer_date DESC\nLIMIT 500;",
            ],
            [
                'name' => '5. รายการสั่งตรวจทางห้องปฏิบัติการ (OPD Lab Orders)',
                'description' => 'รายการตรวจทางห้องปฏิบัติการและผลการตรวจผู้ป่วยนอก',
                'sql' => "SELECT lh.order_date, lh.hn, pt.fname, pt.lname, li.lab_items_name, lr.lab_order_result, li.lab_items_unit\nFROM lab_head lh\nINNER JOIN lab_order lr ON lh.lab_order_number = lr.lab_order_number\nINNER JOIN lab_items li ON lr.lab_items_code = li.lab_items_code\nINNER JOIN patient pt ON lh.hn = pt.hn\nWHERE lh.order_date BETWEEN ':start_date' AND ':end_date'\nORDER BY lh.order_date DESC\nLIMIT 500;",
            ],
        ];

        return view('data_requests.show', compact('dataRequest', 'sqlPresets'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้น');
        }

        $request->validate([
            'status' => 'required|string|in:approved,in_progress,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $dataRequest = DataRequest::findOrFail($id);
        $data = [
            'status' => $request->status,
            'handler_id' => $user->id,
            'admin_notes' => $request->admin_notes ?: $dataRequest->admin_notes,
        ];

        $dataRequest->update($data);

        return back()->with('success', "อัปเดตสถานะคำขอเป็น '{$dataRequest->status_label}' เรียบร้อยแล้ว");
    }

    public function complete(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้น');
        }

        $request->validate([
            'sql_query' => 'nullable|string',
            'admin_notes' => 'nullable|string|max:1000',
            'result_file' => 'nullable|file|max:20480', // 20MB max
        ]);

        $dataRequest = DataRequest::findOrFail($id);

        $updateData = [
            'status' => 'completed',
            'handler_id' => $user->id,
            'sql_query' => $request->sql_query,
            'admin_notes' => $request->admin_notes ?: $dataRequest->admin_notes,
            'completed_at' => now(),
        ];

        if ($request->hasFile('result_file')) {
            $file = $request->file('result_file');
            $filename = 'data_' . $dataRequest->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/data_requests';
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0777, true);
            }
            $file->move(public_path($path), $filename);
            $updateData['result_file'] = $path . '/' . $filename;
        }

        $dataRequest->update($updateData);

        return back()->with('success', "บันทึกข้อมูลและปิดงานคำขอสารสนเทศเรียบร้อยแล้ว ผู้ขอสามารถดาวน์โหลดไฟล์ข้อมูลได้ทันที");
    }

    public function downloadResult($id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::findOrFail($id);

        if ($user->isUser() && $dataRequest->user_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ดาวน์โหลดไฟล์นี้');
        }

        if (empty($dataRequest->result_file) || !file_exists(public_path($dataRequest->result_file))) {
            return back()->withErrors(['download' => 'ไม่พบไฟล์ข้อมูลผลลัพธ์ในระบบ']);
        }

        $dataRequest->increment('download_count');

        AuditLog::record('download', 'data_requests', "ดาวน์โหลดผลลัพธ์คำขอข้อมูล {$dataRequest->request_no} (ครั้งที่ {$dataRequest->download_count})", $dataRequest);

        return response()->download(public_path($dataRequest->result_file));
    }

    public function downloadSample($id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::findOrFail($id);

        if ($user->isUser() && $dataRequest->user_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ดาวน์โหลดไฟล์นี้');
        }

        if (empty($dataRequest->sample_file) || !file_exists(public_path($dataRequest->sample_file))) {
            return back()->withErrors(['download' => 'ไม่พบไฟล์ตัวอย่างที่แนบไว้ในระบบ']);
        }

        AuditLog::record('download_sample', 'data_requests', "ดาวน์โหลดไฟล์ตัวอย่าง/แบบฟอร์มคำขอข้อมูล {$dataRequest->request_no}: {$dataRequest->sample_filename}", $dataRequest);

        return response()->download(
            public_path($dataRequest->sample_file),
            $dataRequest->sample_filename ?: basename($dataRequest->sample_file)
        );
    }

    public function print($id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::with(['user', 'department', 'handler'])->findOrFail($id);

        if ($user->isUser() && $dataRequest->user_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงคำขอข้อมูลสารสนเทศนี้');
        }

        return view('data_requests.print', compact('dataRequest'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::with(['user', 'department'])->findOrFail($id);

        if ($user->isUser() && ($dataRequest->user_id !== $user->id || $dataRequest->status !== 'pending')) {
            abort(403, 'คุณไม่สามารถแก้ไขคำขอนี้ได้เนื่องจากเจ้าหน้าที่รับเรื่องแล้วหรือไม่มีสิทธิ์');
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('data_requests.edit', compact('dataRequest', 'departments', 'user'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::findOrFail($id);

        if ($user->isUser() && ($dataRequest->user_id !== $user->id || $dataRequest->status !== 'pending')) {
            abort(403, 'คุณไม่สามารถแก้ไขคำขอนี้ได้');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'objective_type' => 'required|string|in:ha_quality,research,executive,external,other',
            'objective_detail' => 'nullable|string|max:1000',
            'data_start_date' => 'nullable|date',
            'data_end_date' => 'nullable|date|after_or_equal:data_start_date',
            'criteria_detail' => 'required|string|max:3000',
            'file_format' => 'required|string|in:excel,csv,pdf,text',
            'urgency' => 'required|string|in:normal,urgent,very_urgent',
            'department_id' => 'nullable|exists:it_departments,id',
        ]);

        $dataRequest->update([
            'title' => $request->title,
            'objective_type' => $request->objective_type,
            'objective_detail' => $request->objective_detail,
            'data_start_date' => $request->data_start_date,
            'data_end_date' => $request->data_end_date,
            'criteria_detail' => $request->criteria_detail,
            'file_format' => $request->file_format,
            'urgency' => $request->urgency,
            'department_id' => $request->department_id ?: $dataRequest->department_id,
        ]);

        return redirect()->route('data-requests.show', $dataRequest->id)
            ->with('success', 'บันทึกการแก้ไขคำขอข้อมูลสารสนเทศเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $dataRequest = DataRequest::findOrFail($id);

        if ($user->isUser() && ($dataRequest->user_id !== $user->id || $dataRequest->status !== 'pending')) {
            abort(403, 'ไม่สามารถยกเลิกคำขอนี้ได้');
        }

        if ($dataRequest->result_file && file_exists(public_path($dataRequest->result_file))) {
            @unlink(public_path($dataRequest->result_file));
        }

        $reqNo = $dataRequest->request_no;
        $dataRequest->delete();

        return redirect()->route('data-requests.index')
            ->with('success', "ยกเลิกคำขอข้อมูลสารสนเทศรหัส {$reqNo} เรียบร้อยแล้ว");
    }

    public function executeQuery(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isUser()) {
            return response()->json(['success' => false, 'message' => 'เฉพาะเจ้าหน้าที่สารสนเทศเท่านั้น'], 403);
        }

        $sql = trim($request->input('sql_query', ''));
        if (empty($sql)) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุคำสั่ง SQL'], 422);
        }

        // Security check
        $normalized = strtoupper($sql);
        $forbiddenKeywords = ['INSERT ', 'UPDATE ', 'DELETE ', 'DROP ', 'ALTER ', 'TRUNCATE ', 'REPLACE ', 'GRANT ', 'REVOKE ', 'EXEC '];
        foreach ($forbiddenKeywords as $bad) {
            if (str_contains($normalized, $bad)) {
                return response()->json(['success' => false, 'message' => "ไม่อนุญาตให้ใช้คำสั่ง {$bad} เพื่อความปลอดภัยของระบบ"], 422);
            }
        }

        if (!str_starts_with($normalized, 'SELECT') && !str_starts_with($normalized, 'WITH') && !str_starts_with($normalized, 'SHOW') && !str_starts_with($normalized, 'DESCRIBE') && !str_starts_with($normalized, 'EXPLAIN')) {
            return response()->json(['success' => false, 'message' => 'อนุญาตเฉพาะคำสั่ง SELECT/WITH เท่านั้น'], 422);
        }

        try {
            $hosxpService = app(\App\Services\HosxpService::class);
            $pdo = $hosxpService->getConnection();

            $previewSql = $sql;
            if ((str_starts_with($normalized, 'SELECT') || str_starts_with($normalized, 'WITH')) && !str_contains($normalized, 'LIMIT')) {
                $previewSql = "SELECT * FROM ({$sql}) AS _preview_subquery LIMIT 20";
            }

            $start = microtime(true);
            $stmt = $pdo->query($previewSql);
            $latency = round((microtime(true) - $start) * 1000, 2);

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $columns = [];
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
            }

            return response()->json([
                'success' => true,
                'columns' => $columns,
                'rows' => $rows,
                'total_rows' => count($rows),
                'latency_ms' => $latency,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการรัน SQL: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateFileFromQuery(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่สารสนเทศเท่านั้น');
        }

        $dataRequest = DataRequest::findOrFail($id);
        $sql = trim($request->input('sql_query', ''));
        if (empty($sql)) {
            return back()->withErrors(['sql_query' => 'กรุณาระบุคำสั่ง SQL ก่อนประมวลผลสกัดข้อมูล']);
        }

        // Security check
        $normalized = strtoupper($sql);
        $forbiddenKeywords = ['INSERT ', 'UPDATE ', 'DELETE ', 'DROP ', 'ALTER ', 'TRUNCATE ', 'REPLACE ', 'GRANT ', 'REVOKE '];
        foreach ($forbiddenKeywords as $bad) {
            if (str_contains($normalized, $bad)) {
                return back()->withErrors(['sql_query' => "ไม่อนุญาตให้ใช้คำสั่ง {$bad} เพื่อความปลอดภัยของระบบ"]);
            }
        }

        try {
            $hosxpService = app(\App\Services\HosxpService::class);
            $pdo = $hosxpService->getConnection();

            $stmt = $pdo->query($sql);

            $dir = public_path('uploads/data_requests');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $filename = 'data_req_' . $dataRequest->id . '_' . date('Ymd_His') . '.csv';
            $fullPath = $dir . '/' . $filename;
            $relativeFile = 'uploads/data_requests/' . $filename;

            $fp = fopen($fullPath, 'w');
            fputs($fp, "\xEF\xBB\xBF"); // UTF-8 BOM

            $rowCount = 0;
            $headerWritten = false;
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$headerWritten) {
                    fputcsv($fp, array_keys($row));
                    $headerWritten = true;
                }
                fputcsv($fp, array_values($row));
                $rowCount++;
            }
            fclose($fp);

            if ($rowCount === 0 && !$headerWritten) {
                $fp = fopen($fullPath, 'w');
                fputs($fp, "\xEF\xBB\xBF");
                fputcsv($fp, ['ผลลัพธ์: ไม่พบข้อมูลตามเงื่อนไขที่ระบุ']);
                fclose($fp);
            }

            $dataRequest->update([
                'status' => 'completed',
                'handler_id' => $user->id,
                'sql_query' => $sql,
                'result_file' => $relativeFile,
                'admin_notes' => $request->admin_notes ?: "สกัดข้อมูลจากระบบ HosXP อัตโนมัติเรียบร้อยแล้ว (รวม {$rowCount} รายการ)",
                'completed_at' => now(),
            ]);

            return back()->with('success', "สกัดข้อมูลและสร้างไฟล์ CSV เรียบร้อยแล้ว (จำนวน {$rowCount} แถว) ผู้ขอสามารถดาวน์โหลดไฟล์ได้ทันที");
        } catch (\Exception $e) {
            return back()->withErrors(['sql_query' => 'เกิดข้อผิดพลาดในการประมวลผล SQL: ' . $e->getMessage()]);
        }
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $query = DataRequest::with(['user', 'department', 'handler'])->orderBy('id', 'desc');

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('request_no', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('criteria_detail', 'like', "%{$s}%");
            });
        }

        $list = $query->get();
        $filename = 'data_requests_' . date('Ymd_His') . '.csv';

        AuditLog::record('export', 'data_requests', "ส่งออกรายงานคำขอข้อมูลสารสนเทศเป็นไฟล์ CSV (จำนวน " . count($list) . " รายการ)", null);

        return response()->streamDownload(function () use ($list) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'เลขที่คำขอ',
                'วันที่ยื่นคำขอ',
                'หัวข้อข้อมูลสารสนเทศ',
                'วัตถุประสงค์',
                'รายละเอียดวัตถุประสงค์',
                'หน่วยงาน/แผนก',
                'ผู้ยื่นคำขอ',
                'เบอร์โทรศัพท์',
                'ช่วงเวลาข้อมูลเริ่มต้น',
                'ช่วงเวลาข้อมูลสิ้นสุด',
                'รูปแบบไฟล์',
                'ความเร่งด่วน',
                'สถานะ',
                'เจ้าหน้าที่ผู้รับผิดชอบ',
                'วันที่ดำเนินการเสร็จ',
                'หมายเหตุ/ผลลัพธ์',
            ]);

            foreach ($list as $r) {
                fputcsv($handle, [
                    $r->request_no,
                    $r->created_at->format('Y-m-d H:i:s'),
                    $r->title,
                    $r->objective_label,
                    $r->objective_detail ?? '-',
                    $r->department->name ?? '-',
                    $r->user->name ?? '-',
                    $r->user->phone ?? '-',
                    $r->data_start_date ? $r->data_start_date->format('Y-m-d') : '-',
                    $r->data_end_date ? $r->data_end_date->format('Y-m-d') : '-',
                    strtoupper($r->file_format),
                    $r->urgency_label,
                    $r->status_label,
                    $r->handler->name ?? '-',
                    $r->completed_at ? $r->completed_at->format('Y-m-d H:i:s') : '-',
                    $r->admin_notes ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
