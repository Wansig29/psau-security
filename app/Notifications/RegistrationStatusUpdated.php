<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationStatusUpdated extends Notification
{
    use Queueable;

    public $registration;
    public $statusType;
    public $message;

    public function __construct($registration, $statusType, $message)
    {
        $this->registration = $registration;
        $this->statusType = $statusType; // 'approved' or 'rejected'
        $this->message = $message;
    }

    public function via($notifiable)
    {
        // Sends both a Facebook-style DB notification AND an email.
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('PSAU Security: Sticker Registration ' . ucfirst($this->statusType))
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line($this->message)
            ->action('View My Dashboard', url('/user/dashboard'))
            ->line('Thank you for using the PSAU Security Management System.');
    }

    public function toArray($notifiable)
    {
        return [
            'registration_id' => $this->registration->id ?? null,
            'vehicle_name' => $this->registration->vehicle->make . ' ' . $this->registration->vehicle->model,
            'plate_number' => $this->registration->vehicle->plate_number,
            'status' => $this->statusType,
            'message' => $this->message,
        ];
    }
}
