<?php

namespace admin\admin_auth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckAdminAuthModuleStatusCommand extends Command
{
    protected $signature = 'admin_auth:check-status';
    protected $description = 'Check the status of the admin_auth module (published, key files, etc.)';

    public function handle()
    {
        $modulePath = base_path('Modules/AdminAuth');
        $status = File::exists($modulePath) ? 'PUBLISHED' : 'NOT PUBLISHED';
        $this->info("AdminAuth module status: $status");

        $required = [
            'app/Http/Controllers/Admin',
            'app/Models',
            'app/Http/Requests',
            'routes/web.php',
            'resources/views',
            'database/migrations',
        ];
        foreach ($required as $rel) {
            $full = $modulePath . '/' . $rel;
            $exists = File::exists($full);
            $this->line(" - $rel: " . ($exists ? 'FOUND' : 'MISSING'));
        }
    }
} 