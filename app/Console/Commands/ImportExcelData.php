<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ImportExcelData extends Command
{
    protected $signature = 'app:import-excel-data {file=dummy_data_visit_report_telkom_jatinegara.xlsx}';
    protected $description = 'Import dummy data visit report telkom jatinegara from Excel';

    public function handle(): int
    {
        $filePath = base_path($this->argument('file'));
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Loading workbook: {$filePath}...");
        $spreadsheet = IOFactory::load($filePath);

        DB::beginTransaction();
        try {
            // 1. Witel
            $this->importSheet($spreadsheet, 'witel', 'witel', ['id', 'name']);

            // 2. Telda
            $this->importSheet($spreadsheet, 'telda', 'telda', ['id', 'name', 'witel_id']);

            // 3. Role
            $this->importSheet($spreadsheet, 'role', 'role', ['id', 'name']);

            // 4. Activity Type
            $this->importSheet($spreadsheet, 'activity_type', 'activity_type', ['id', 'name']);

            // 5. Activity Category
            $this->importSheet($spreadsheet, 'activity_category', 'activity_category', ['id', 'name']);

            // 6. R-Level
            $this->importSheet($spreadsheet, 'r_level', 'r_level', ['id', 'name', 'sort_order']);

            // 7. Service Category
            $this->importSheet($spreadsheet, 'service_category', 'service_category', ['id', 'name']);

            // 8. Service
            $this->importSheet($spreadsheet, 'service', 'service', ['id', 'name', 'service_category_id']);

            // 9. Business Customer
            $this->importSheet($spreadsheet, 'business_customer', 'business_customer', [
                'id', 'name', 'nipnas', 'status', 'telda_id', 'service_id',
                'default_pic_name', 'default_pic_contact', 'address', 'latitude', 'longitude', 'segment', 'created_at'
            ]);

            // 10. Employee
            $sheet = $spreadsheet->getSheetByName('employee');
            $rows = $sheet ? $sheet->toArray(null, true, true, false) : [];
            $headers = array_map('trim', array_map('strval', $rows[0] ?? []));
            $empData = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!isset($row[0]) || $row[0] === null || $row[0] === '') continue;
                $id = (int)$row[0];
                $name = (string)($row[1] ?? 'Employee ' . $id);
                $email = $row[2] ?? null;
                if (empty($email)) {
                    $slug = \Illuminate\Support\Str::slug($name, '.');
                    $email = (!empty($slug) ? $slug : 'am' . $id) . $id . '@telkom.co.id';
                }
                $empData[] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => strtolower($email),
                    'phone' => $row[3] ?: null,
                    'role_id' => $row[4] ? (int)$row[4] : 1,
                    'telda_id' => $row[5] ? (int)$row[5] : null,
                    'is_active' => true,
                    'nip' => $row[7] ?: null,
                    'supervisor_id' => $row[8] ? (int)$row[8] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('employee')->upsert($empData, ['id']);
            $this->info("Imported " . count($empData) . " rows to [employee].");

            // 11. Visit Report
            $this->importSheet($spreadsheet, 'visit_report', 'visit_report', [
                'id', 'employee_id', 'business_customer_id', 'customer_pic_name',
                'activity_type_id', 'r_level_id', 'activity_category_id', 'estimated_value',
                'activity_description', 'action_plan', 'voc', 'visit_type', 'visit_date',
                'visit_time', 'validation_status', 'validator_id', 'validation_notes',
                'validated_at', 'created_at'
            ]);

            // 12. Visit Report Service
            $this->importSheet($spreadsheet, 'visit_report_service', 'visit_report_service', [
                'id', 'visit_report_id', 'service_id'
            ]);

            // Create Default Admin User
            $admin = User::updateOrCreate(
                ['email' => 'admin@telkom.co.id'],
                [
                    'name' => 'Administrator Telkom',
                    'password' => Hash::make('password'),
                    'role' => 'Admin',
                ]
            );
            $this->info("Default Admin created: {$admin->email} / password");

            // Create User accounts for each Employee
            $employees = DB::table('employee')->get();
            foreach ($employees as $emp) {
                User::updateOrCreate(
                    ['email' => $emp->email],
                    [
                        'name' => $emp->name,
                        'password' => Hash::make('password'),
                        'role' => 'AM',
                        'employee_id' => $emp->id,
                    ]
                );
            }
            $this->info("Created users for {$employees->count()} employees (Password: password)");

            // Reset PostgreSQL sequence counters
            $tables = [
                'witel', 'telda', 'role', 'activity_type', 'activity_category',
                'r_level', 'service_category', 'service', 'business_customer',
                'employee', 'visit_report', 'visit_report_service'
            ];
            foreach ($tables as $table) {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), coalesce(max(id), 1), max(id) IS NOT null) FROM {$table};");
            }

            DB::commit();
            $this->info("All data successfully imported from Excel!");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error importing data: " . $e->getMessage());
            return 1;
        }
    }

    private function importSheet($spreadsheet, string $sheetName, string $tableName, array $columns): void
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (!$sheet) {
            $this->warn("Sheet {$sheetName} not found, skipping.");
            return;
        }

        $rows = $sheet->toArray(null, true, true, false);
        if (count($rows) <= 1) {
            $this->warn("Sheet {$sheetName} is empty.");
            return;
        }

        $headers = array_map('trim', array_map('strval', $rows[0]));
        $colMap = [];
        foreach ($columns as $col) {
            $idx = array_search($col, $headers);
            if ($idx !== false) {
                $colMap[$col] = $idx;
            }
        }

        $data = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (!isset($row[0]) || $row[0] === null || $row[0] === '') {
                continue;
            }

            $record = [];
            foreach ($colMap as $col => $idx) {
                $val = $row[$idx] ?? null;
                if ($val === '') {
                    $val = null;
                }

                if (in_array($col, ['is_active'])) {
                    $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }

                $record[$col] = $val;
            }
            $record['created_at'] = $record['created_at'] ?? now();
            $record['updated_at'] = now();

            $data[] = $record;
        }

        foreach (array_chunk($data, 100) as $chunk) {
            DB::table($tableName)->upsert($chunk, ['id']);
        }

        $this->info("Imported " . count($data) . " rows to [{$tableName}].");
    }
}
