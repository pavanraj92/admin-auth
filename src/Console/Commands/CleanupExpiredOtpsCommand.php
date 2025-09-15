<?php

namespace admin\admin_auth\Console\Commands;

use Illuminate\Console\Command;
use admin\admin_auth\Services\OtpService;

class CleanupExpiredOtpsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:cleanup-otps {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP codes from the database';

    /**
     * Execute the console command.
     */
    public function handle(OtpService $otpService)
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to clean up expired OTPs?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('Cleaning up expired OTPs...');
        
        $deletedCount = $otpService->cleanupExpiredOtps();
        
        $this->info("Successfully cleaned up {$deletedCount} expired OTP records.");
        
        return Command::SUCCESS;
    }
}
