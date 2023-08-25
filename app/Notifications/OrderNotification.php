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

class OrderNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $bookingId;

    public function __construct($title, $body, $bookingId)
    {
        $this->title = $title;
        $this->body = $body;
        $this->bookingId = $bookingId;
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
            'title' => $this->title,
            'body' => $this->body,
            'booking_id' => $this->bookingId, // Menyimpan ID booking dalam data notifikasi
        ])
        ->setNotification(FcmNotification::create()
            ->setTitle($this->title)
            ->setBody($this->body))
        ->setAndroid(AndroidConfig::create()
            ->setNotification(AndroidNotification::create()
                ->setClickAction('FLUTTER_NOTIFICATION_CLICK')
                ->setColor('#FF0000')
                ->setIcon('ic_launcher')
                ->setTag('booking-request')
                ->setChannelId('booking-channel')
                ->setBodyLocArgs([$this->bookingId])
            )
        )
        ->setWebpush(WebpushConfig::create()
            ->setFcmOptions(WebpushFcmOptions::create()
                ->setLink('/booking/' . $this->bookingId)
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
