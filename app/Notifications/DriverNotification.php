<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\WebpushConfig;
use NotificationChannels\Fcm\Resources\WebpushFcmOptions;

class DriverNotification extends Notification
{
    use Queueable;

    protected $driverData;
    protected $distance;
    protected $idBooking;

    public function __construct($driverData, $distance, $idBooking)
    {
        $this->driverData = $driverData;
        $this->distance = $distance;
        $this->idBooking = $idBooking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
        ->setData([
            'title' => 'New Booking Request',
            'body' => 'You have a new booking request. ID: ' . $this->idBooking,
            'booking_id' => $this->idBooking, // Menyimpan ID booking dalam data notifikasi
            'action' => 'accept_reject', // Menyimpan tipe tindakan
        ])
        ->setNotification(FcmNotification::create()
            ->setTitle('New Booking Request')
            ->setBody('You have a new booking request. ID: ' . $this->idBooking))
        ->setAndroid(AndroidConfig::create()
            ->setNotification(AndroidNotification::create()
                ->setClickAction('FLUTTER_NOTIFICATION_CLICK')
                ->setColor('#FF0000')
                ->setIcon('ic_launcher')
                ->setTag('booking-request')
                ->setChannelId('booking-channel')
                ->setBodyLocArgs([$this->idBooking])
            )
        )
        ->setWebpush(WebpushConfig::create()
            ->setFcmOptions(WebpushFcmOptions::create()
                ->setLink('/booking/' . $this->idBooking)
            )
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
