<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Services\ItAssetCodeGeneratorService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. เพิ่มฟิลด์ it_code ในตาราง it_assets สำหรับเก็บรหัสคุมครุภัณฑ์ไอที (IT Asset Tag)
        if (!Schema::hasColumn('it_assets', 'it_code')) {
            Schema::table('it_assets', function (Blueprint $table) {
                $table->string('it_code', 50)->nullable()->index()->after('asset_code')->comment('รหัสคุมครุภัณฑ์ไอที (IT Asset Tag)');
            });
        }

        // 2. ซิงค์และสร้างรหัสอัตโนมัติสำหรับข้อมูลครุภัณฑ์เดิมที่มีอยู่แล้วในระบบ
        try {
            $assets = Asset::with(['department', 'deviceType'])->get();
            foreach ($assets as $asset) {
                $code = null;
                // หากเคยมีการระบุแท็กปรับแต่งไว้ใน notes เช่น [IT-TAG: ER-PC-67-001]
                if (!empty($asset->notes) && preg_match('/\[IT-TAG:\s*([^\]]+)\]/', $asset->notes, $matches)) {
                    $code = trim($matches[1]);
                }

                // หากยังไม่มี ให้สร้างรหัสอัตโนมัติ (Smart Auto-Bound IT Code)
                if (empty($code)) {
                    $code = ItAssetCodeGeneratorService::generateAutoBoundCode($asset);
                }

                if (!empty($code)) {
                    $cleanCode = str_replace('-', '', $code);
                    DB::table('it_assets')->where('id', $asset->id)->update([
                        'it_code' => $cleanCode
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // ไม่ให้ขัดขวาง migration กรณี database ยังไม่พร้อมเต็มที่
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('it_assets', 'it_code')) {
            Schema::table('it_assets', function (Blueprint $table) {
                $table->dropColumn('it_code');
            });
        }
    }
};
