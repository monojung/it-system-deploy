<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_assets', function (Blueprint $table) {
            $table->string('cpu_model', 150)->nullable()->index()->after('specs');
            $table->string('cpu_speed', 50)->nullable()->after('cpu_model');
            $table->integer('ram_capacity')->nullable()->index()->after('cpu_speed'); // in GB (e.g. 4, 8, 16, 32, 64)
            $table->string('ram_type', 30)->nullable()->index()->after('ram_capacity'); // DDR4, DDR5, DDR3
            $table->string('ram_bus', 30)->nullable()->after('ram_type'); // e.g. 3200 MHz, 4800 MHz
            $table->string('ram_slots', 50)->nullable()->after('ram_bus'); // e.g. 1 Slot (16GB x 1), 2 Slots (8GB x 2)
            $table->string('storage_type', 50)->nullable()->index()->after('ram_slots'); // SSD NVMe M.2, SSD SATA, HDD SATA
            $table->string('storage_capacity', 50)->nullable()->index()->after('storage_type'); // 256 GB, 512 GB, 1 TB
            $table->string('storage_second', 100)->nullable()->after('storage_capacity'); // e.g. HDD 1 TB Data
            $table->string('os_name', 100)->nullable()->index()->after('storage_second'); // Windows 11 Pro, Windows 10 Pro, etc.
            $table->string('os_license', 100)->nullable()->after('os_name'); // OEM, Volume, Retail, None
            $table->string('gpu_model', 150)->nullable()->after('os_license'); // Intel UHD, Iris Xe, RTX 3060
            $table->string('monitor_size', 50)->nullable()->after('gpu_model'); // 14", 15.6", 21.5", 23.8", 24"
        });

        // Migrate existing computer specs to structured columns
        $assets = DB::table('it_assets')->get();
        foreach ($assets as $asset) {
            $specs = $asset->specs ?? '';
            $updates = [];

            // CPU detection
            if (preg_match('/(Intel\s+Core\s+i[3579]-[\w]+|Core\s+i[3579]-[\w]+|AMD\s+Ryzen\s+[3579]\s+[\w]+)/i', $specs, $m)) {
                $updates['cpu_model'] = $m[1];
            }

            // RAM capacity
            if (preg_match('/RAM\s*(\d+)\s*GB/i', $specs, $m)) {
                $updates['ram_capacity'] = (int)$m[1];
            }

            // RAM type default
            if (isset($updates['ram_capacity'])) {
                if (stripos($specs, 'DDR5') !== false) {
                    $updates['ram_type'] = 'DDR5';
                } elseif (stripos($specs, 'DDR3') !== false) {
                    $updates['ram_type'] = 'DDR3';
                } else {
                    // Default modern hospital PCs to DDR4 if not specified
                    $updates['ram_type'] = 'DDR4';
                }
            }

            // Storage
            if (preg_match('/(SSD\s+NVMe|NVMe\s+SSD|SSD\s+SATA|SSD|HDD)/i', $specs, $m)) {
                $type = strtoupper($m[1]);
                if (str_contains($type, 'NVME')) {
                    $updates['storage_type'] = 'SSD NVMe M.2';
                } elseif (str_contains($type, 'HDD')) {
                    $updates['storage_type'] = 'HDD SATA 3.5"';
                } else {
                    $updates['storage_type'] = 'SSD SATA 2.5"';
                }
            }
            if (preg_match('/(?:SSD|HDD|NVMe)?\s*(\d+\s*(?:GB|TB))/i', $specs, $m)) {
                $updates['storage_capacity'] = strtoupper(trim($m[1]));
            }

            // OS
            if (preg_match('/(Windows\s+11\s+Pro|Windows\s+11\s+Home|Windows\s+11|Windows\s+10\s+Pro|Windows\s+10\s+Home|Windows\s+10|Windows\s+7)/i', $specs, $m)) {
                $os = $m[1];
                if ($os === 'Windows 11') $os = 'Windows 11 Pro';
                if ($os === 'Windows 10') $os = 'Windows 10 Pro';
                $updates['os_name'] = $os;
                $updates['os_license'] = 'OEM (ติดเครื่อง/เมนบอร์ด)';
            }

            // Monitor size
            if (preg_match('/(\d+(?:\.\d+)?\s*(?:Inch|นิ้ว))/i', $specs, $m)) {
                $updates['monitor_size'] = $m[1];
            }

            if (!empty($updates)) {
                DB::table('it_assets')->where('id', $asset->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        Schema::table('it_assets', function (Blueprint $table) {
            $table->dropColumn([
                'cpu_model',
                'cpu_speed',
                'ram_capacity',
                'ram_type',
                'ram_bus',
                'ram_slots',
                'storage_type',
                'storage_capacity',
                'storage_second',
                'os_name',
                'os_license',
                'gpu_model',
                'monitor_size',
            ]);
        });
    }
};
