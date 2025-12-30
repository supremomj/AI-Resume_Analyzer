<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOTP extends Notification
{
    // Removed Queueable to ensure immediate (real-time) delivery

    /**
     * The OTP code.
     *
     * @var string
     */
    public $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Email - HanapBuh.AI')
            ->greeting('Hello ' . ($notifiable->first_name ?? 'there') . '!')
            ->line('Thank you for registering with HanapBuh.AI!')
            ->line('Please use the following OTP code to verify your email address:')
            ->line('**' . $this->otp . '**')
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not create an account, please ignore this email.')
            ->salutation('Best regards, The HanapBuh.AI Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
        ];
    }
}
