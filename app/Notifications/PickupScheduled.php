<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PickupScheduled extends Notification
{
    use Queueable;

    public $registration;
    public $schedule;

    public function __construct($registration, $schedule)
    {
        $this->registration = $registration;
        $this->schedule = $schedule;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('PSAU Security: Sticker Pick-Up Scheduled')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('Your vehicle sticker is ready for pick-up!')
            ->line('Date: ' . \Carbon\Carbon::parse($this->schedule->pickup_date)->format('F d, Y'))
            ->line('Time: ' . \Carbon\Carbon::parse($this->schedule->pickup_time)->format('h:i A'))
            ->line('Location: ' . $this->schedule->location)
            ->action('View My Dashboard', url('/user/dashboard'))
            ->line('Please bring your valid ID and vehicle documents upon claiming.');
    }

    public function toArray($notifiable)
    {
        return [
            'registration_id' => $this->registration->id ?? null,
            'vehicle_name' => $this->registration->vehicle->make . ' ' . $this->registration->vehicle->model,
            'plate_number' => $this->registration->vehicle->plate_number,
            'status' => 'pickup_scheduled',
            'message' => 'Your sticker pick-up is scheduled on ' . \Carbon\Carbon::parse($this->schedule->pickup_date)->format('M d') . ' at ' . \Carbon\Carbon::parse($this->schedule->pickup_time)->format('h:i A'),
        ];
    }
}
