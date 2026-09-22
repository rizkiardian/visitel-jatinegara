<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Witel
        Schema::create('witel', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 2. Telda
        Schema::create('telda', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('witel_id')->nullable()->constrained('witel')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Role
        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 4. Activity Type
        Schema::create('activity_type', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 5. Activity Category
        Schema::create('activity_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 6. R-Level
        Schema::create('r_level', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 7. Service Category
        Schema::create('service_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 8. Service
        Schema::create('service', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('service_category_id')->nullable()->constrained('service_category')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Business Customer
        Schema::create('business_customer', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nipnas')->nullable();
            $table->string('status')->default('New'); // New, Existing
            $table->foreignId('telda_id')->nullable()->constrained('telda')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('service')->nullOnDelete();
            $table->string('default_pic_name')->nullable();
            $table->string('default_pic_contact')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('segment')->nullable();
            $table->timestamps();
        });

        // 10. Employee
        Schema::create('employee', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->foreignId('role_id')->nullable()->constrained('role')->nullOnDelete();
            $table->foreignId('telda_id')->nullable()->constrained('telda')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('nip')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('employee')->nullOnDelete();
            $table->timestamps();
        });

        // Add employee_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained('employee')->nullOnDelete();
            $table->string('role')->default('AM'); // Admin, Supervisor, AM, AR
        });

        // 11. Visit Report
        Schema::create('visit_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employee')->cascadeOnDelete();
            $table->foreignId('business_customer_id')->constrained('business_customer')->cascadeOnDelete();
            $table->string('customer_pic_name')->nullable();
            $table->foreignId('activity_type_id')->nullable()->constrained('activity_type')->nullOnDelete();
            $table->foreignId('r_level_id')->nullable()->constrained('r_level')->nullOnDelete();
            $table->foreignId('activity_category_id')->nullable()->constrained('activity_category')->nullOnDelete();
            $table->decimal('estimated_value', 15, 2)->nullable()->default(0);
            $table->text('activity_description')->nullable();
            $table->text('action_plan')->nullable();
            $table->text('voc')->nullable();
            $table->string('visit_type')->default('Visit'); // Visit, NonVisit
            $table->date('visit_date');
            $table->time('visit_time')->nullable();
            $table->string('validation_status')->default('Pending'); // Pending, Valid, Rejected
            $table->foreignId('validator_id')->nullable()->constrained('employee')->nullOnDelete();
            $table->text('validation_notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        // 12. Visit Report Service Junction
        Schema::create('visit_report_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained('visit_report')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('service')->cascadeOnDelete();
            $table->timestamps();
        });

        // 13. Report Location
        Schema::create('report_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained('visit_report')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });

        // 14. Report Photo
        Schema::create('report_photo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained('visit_report')->cascadeOnDelete();
            $table->string('photo_type'); // LocationPhoto, PhotoWithPIC
            $table->string('file_url');
            $table->timestamp('uploaded_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        // 15. Attendance
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employee')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('status')->default('OnTime'); // OnTime, Late
            $table->integer('late_minutes')->default(0);
            $table->string('day_type')->default('Weekday'); // Weekday, Weekend, Holiday
            $table->boolean('is_mandatory')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('report_photo');
        Schema::dropIfExists('report_location');
        Schema::dropIfExists('visit_report_service');
        Schema::dropIfExists('visit_report');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'role']);
        });
        Schema::dropIfExists('employee');
        Schema::dropIfExists('business_customer');
        Schema::dropIfExists('service');
        Schema::dropIfExists('service_category');
        Schema::dropIfExists('r_level');
        Schema::dropIfExists('activity_category');
        Schema::dropIfExists('activity_type');
        Schema::dropIfExists('role');
        Schema::dropIfExists('telda');
        Schema::dropIfExists('witel');
    }
};
