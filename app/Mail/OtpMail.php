<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $type;

    public function __construct(string $otp, string $type = 'REGISTER')
    {
        $this->otp = $otp;
        $this->type = $type;
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'FORGOT_PASSWORD'
            ? 'Reset Your Password - OTP Code'
            : 'Verify Your Email - OTP Code';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        $title = $this->type === 'FORGOT_PASSWORD' ? 'Reset Password' : 'Email Verification';
        $message = $this->type === 'FORGOT_PASSWORD'
            ? 'You requested to reset your password. Use the OTP below:'
            : 'Welcome! Please verify your email with the OTP below:';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4F46E5; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-box { background: white; border: 2px dashed #4F46E5; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .otp-code { font-size: 36px; font-weight: bold; color: #4F46E5; letter-spacing: 8px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$title}</h1>
                </div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>{$message}</p>
                    <div class='otp-box'>
                        <p style='margin: 0; color: #666;'>Your OTP Code:</p>
                        <div class='otp-code'>{$this->otp}</div>
                        <p style='margin-top: 10px; color: #666; font-size: 14px;'>Valid for 5 minutes</p>
                    </div>
                    <p><strong>Important:</strong> Do not share this code with anyone.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Watch Store. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
