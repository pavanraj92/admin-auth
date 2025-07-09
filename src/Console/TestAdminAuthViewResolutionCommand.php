<?php

namespace admin\admin_auth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class TestAdminAuthViewResolutionCommand extends Command
{
    protected $signature = 'admin_auth:test-view';
    protected $description = 'Test view resolution for the admin_auth module';

    public function handle()
    {
        $viewName = 'admin.dashboard'; // Example view name
        $found = View::exists($viewName);
        if ($found) {
            $this->info("View '$viewName' was found and can be rendered.");
        } else {
            $this->error("View '$viewName' was NOT found. Check your module publish and view paths.");
        }
    }
} 