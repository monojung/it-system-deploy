<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create it_budget_sources table (แหล่งเงิน / ประเภทเงินที่ใช้ซื้อ)
        Schema::create('it_budget_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // เช่น เงิน UC, เงินบำรุง, เงินงบประมาณแผ่นดิน
            $table->string('code', 50)->nullable()->unique(); // UC, REVENUE, GOV_BUDGET, DONATION
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create it_acquisition_methods table (วิธีการได้มาของครุภัณฑ์)
        Schema::create('it_acquisition_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // เช่น จัดซื้อจัดจ้าง, บริจาค, เช่าใช้
            $table->string('code', 50)->nullable()->unique(); // PURCHASE, DONATION, TRANSFER_IN, RENTAL
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Add acquisition, funding, and rental lease control columns to it_assets
        Schema::table('it_assets', function (Blueprint $table) {
            $table->foreignId('budget_source_id')->nullable()->after('budget_year')->constrained('it_budget_sources')->nullOnDelete();
            $table->foreignId('acquisition_method_id')->nullable()->after('budget_source_id')->constrained('it_acquisition_methods')->nullOnDelete();
            $table->string('ownership_type', 30)->default('owned')->index()->after('acquisition_method_id'); // owned, rented, donated, borrowed
            $table->string('rental_contract_no', 100)->nullable()->after('ownership_type'); // เลขที่สัญญาเช่า
            $table->string('rental_vendor', 150)->nullable()->after('rental_contract_no'); // บริษัทผู้ให้เช่า / คู่สัญญา
            $table->date('rental_start_date')->nullable()->after('rental_vendor'); // วันเริ่มสัญญา
            $table->date('rental_end_date')->nullable()->after('rental_start_date'); // วันสิ้นสุดสัญญา
            $table->decimal('rental_monthly_fee', 12, 2)->nullable()->after('rental_end_date'); // ค่าเช่ารายเดือน/ต่องวด
            $table->string('rental_contact_phone', 50)->nullable()->after('rental_monthly_fee'); // เบอร์ติดต่อบริษัท/ช่าง
            $table->text('rental_conditions')->nullable()->after('rental_contact_phone'); // เงื่อนไขสัญญาเช่า (เช่น ฟรีหมึกพิมพ์ โควตากี่แผ่น)
        });

        // 4. Seed initial standard Thai hospital master data
        $now = now();
        $defaultBudgetSources = [
            ['name' => 'เงิน UC (กองทุนหลักประกันสุขภาพถ้วนหน้า)', 'code' => 'UC', 'description' => 'งบกองทุนหลักประกันสุขภาพถ้วนหน้าเพื่อการบริการปฐมภูมิและทุติยภูมิ', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'เงินบำรุง / เงินเก็บค่าบริการทางการแพทย์', 'code' => 'REVENUE', 'description' => 'รายได้เงินบำรุงของโรงพยาบาลทุ่งหัวช้างจากการให้บริการ', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'เงินงบประมาณแผ่นดิน (งบลงทุน/จัดสรร)', 'code' => 'GOV_BUDGET', 'description' => 'งบประมาณแผ่นดินประจำปีจากสำนักงบประมาณ / กระทรวงสาธารณสุข', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'เงินบริจาค / กองทุนพัฒนาโรงพยาบาล', 'code' => 'DONATION', 'description' => 'เงินที่ได้รับบริจาคจากผู้มีจิตศรัทธาหรือกองทุนพัฒนาโรงพยาบาล', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'เงินโครงการเฉพาะกิจ / สสส. / อื่นๆ', 'code' => 'OTHER', 'description' => 'งบสนับสนุนโครงการวิจัย นวัตกรรม หรือกองทุนเฉพาะกิจ', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('it_budget_sources')->insert($defaultBudgetSources);

        $defaultAcquisitionMethods = [
            ['name' => 'จัดซื้อจัดจ้าง (เงินงบประมาณ/เงินบำรุง)', 'code' => 'PURCHASE', 'description' => 'การจัดซื้อจัดจ้างตามระเบียบพัสดุภาครัฐ', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ได้รับบริจาค (Donation)', 'code' => 'DONATION', 'description' => 'ได้รับมอบบริจาคจากองค์กร ประชาชน หรือมูลนิธิ', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'รับโอนจากหน่วยงานอื่น / ส่วนกลาง', 'code' => 'TRANSFER_IN', 'description' => 'รับโอนกรรมสิทธิ์ครุภัณฑ์จาก สสจ. หรือกระทรวงสาธารณสุข', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'เช่าใช้ / สัญญาเช่าบริการ (Lease / Rental)', 'code' => 'RENTAL', 'description' => 'ครุภัณฑ์เช่าใช้ เช่น เครื่องพิมพ์เช่า เครื่องถ่ายเอกสาร พร้อมบริการซ่อมและหมึก', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ยืมทดลองใช้งาน (Trial / Loan)', 'code' => 'TRIAL', 'description' => 'เครื่องสาธิตหรือยืมใช้งานชั่วคราวระหว่างรอจัดซื้อ', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('it_acquisition_methods')->insert($defaultAcquisitionMethods);
    }

    public function down(): void
    {
        Schema::table('it_assets', function (Blueprint $table) {
            $table->dropForeign(['budget_source_id']);
            $table->dropForeign(['acquisition_method_id']);
            $table->dropColumn([
                'budget_source_id',
                'acquisition_method_id',
                'ownership_type',
                'rental_contract_no',
                'rental_vendor',
                'rental_start_date',
                'rental_end_date',
                'rental_monthly_fee',
                'rental_contact_phone',
                'rental_conditions',
            ]);
        });

        Schema::dropIfExists('it_acquisition_methods');
        Schema::dropIfExists('it_budget_sources');
    }
};
