<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'name',
        'password',
        'otp',
        'type',
        'is_used',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Generate random 6-digit OTP
     */
    public static function generateOtp(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create new OTP for email
     */
    public static function createOtp(string $email, string $type = 'REGISTER'): self
    {
        // Delete old unused OTPs
        self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        return self::create([
            'email' => $email,
            'otp' => self::generateOtp(),
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(5), // OTP valid for 5 minutes
        ]);
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp(string $email, string $otp, string $type = 'REGISTER'): bool
    {
        $otpRecord = self::where('email', $email)
            ->where('otp', $otp)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otpRecord) {
            $otpRecord->update(['is_used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
