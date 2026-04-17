<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApproved extends Notification
{
    use Queueable;

    public $registration;

    public function __construct($registration)
    {
        $this->registration = $registration;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('PSAU Security: Your Vehicle Registration is Approved!')
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('Your vehicle registration has been approved.')
            ->line('Plate Number: ' . ($this->registration->vehicle->plate_number ?? 'N/A'))
            ->action('View My Dashboard', url('/user/dashboard'))
            ->line('Please visit the admin office to claim your parking sticker.');
    }

    public function toArray($notifiable)
    {
        return [
            'registration_id' => $this->registration->id ?? null,
            'vehicle_name'    => ($this->registration->vehicle->make ?? '') . ' ' . ($this->registration->vehicle->model ?? ''),
            'plate_number'    => $this->registration->vehicle->plate_number ?? null,
            'status'          => 'approved',
            'message'         => 'Your vehicle registration has been approved!',
        ];
    }
}
