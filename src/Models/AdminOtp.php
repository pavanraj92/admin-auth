<?php

namespace admin\admin_auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AdminOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'otp_code',
        'expires_at',
        'is_used',
        'type'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Check if OTP is valid (not expired and not used)
     */
    public function isValid()
    {
        return !$this->isExpired() && !$this->is_used;
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    /**
     * Get time remaining in seconds
     */
    public function getTimeRemaining()
    {
        $remaining = $this->expires_at->diffInSeconds(now(), false);
        return max(0, $remaining);
    }

    /**
     * Get time remaining in minutes and seconds
     */
    public function getTimeRemainingFormatted()
    {
        $totalSeconds = $this->getTimeRemaining();
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Relationship with Admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope to get valid OTPs
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired OTPs
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired()
    {
        return static::expired()->delete();
    }
}
