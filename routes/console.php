<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('invoices:mark-overdue')->daily();
