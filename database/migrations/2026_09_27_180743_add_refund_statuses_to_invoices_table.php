<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','partially_paid','paid','overdue','cancelled','partially_refunded','refunded') DEFAULT 'draft'");
    }

    public function down(): void
    {
        // Warning: down() will fail if any invoice has the status 'refunded' or 'partially_refunded'
        // (because those values are not in the old ENUM list). Update those rows manually before rolling back.
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','partially_paid','paid','overdue','cancelled') DEFAULT 'draft'");
    }
};
