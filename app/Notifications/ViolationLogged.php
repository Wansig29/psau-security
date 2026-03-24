<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViolationLogged extends Notification
{
    use Queueable;

    public $violation;
    public $sanctionDescription;

    public function __construct($violation, $sanctionDescription)
    {
        $this->violation = $violation;
        $this->sanctionDescription = $sanctionDescription;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('PSAU Security: Violation Notice Issued')
            ->greeting('Hello, ' . $notifiable->name . '.')
            ->line('A new security violation has been logged for your vehicle (' . $this->violation->vehicle->plate_number . ').')
            ->line('Offense: ' . ucwords(str_replace('_', ' ', $this->violation->violation_type)))
            ->line('Sanction Applied: ' . $this->sanctionDescription)
            ->action('View Violation Details', url('/user/dashboard'))
            ->line('Please visit the security office if you wish to appeal this sanction.');
    }

    public function toArray($notifiable)
    {
        return [
            'violation_id' => $this->violation->id,
            'vehicle_name' => $this->violation->vehicle->make . ' ' . $this->violation->vehicle->model,
            'plate_number' => $this->violation->vehicle->plate_number,
            'status' => 'violation',
            'message' => 'A new violation was logged: ' . $this->sanctionDescription,
        ];
    }
}
