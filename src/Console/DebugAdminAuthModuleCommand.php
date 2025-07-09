<?php

namespace admin\admin_auth\Console;

use Illuminate\Console\Command;

class DebugAdminAuthModuleCommand extends Command
{
    protected $signature = 'admin_auth:debug';
    protected $description = 'Debug the admin_auth module (show paths, config, etc.)';

    public function handle()
    {
        $this->info('AdminAuth Module Debug Info:');
        $this->line('Module Path: ' . base_path('Modules/AdminAuth'));
        $this->line('Controllers: ' . base_path('Modules/AdminAuth/app/Http/Controllers/Admin'));
        $this->line('Models: ' . base_path('Modules/AdminAuth/app/Models'));
        $this->line('Requests: ' . base_path('Modules/AdminAuth/app/Http/Requests'));
        $this->line('Views: ' . base_path('Modules/AdminAuth/resources/views'));
        $this->line('Routes: ' . base_path('Modules/AdminAuth/routes/web.php'));
        $this->line('Migrations: ' . base_path('Modules/AdminAuth/database/migrations'));
        // Add more debug info as needed
    }
} 