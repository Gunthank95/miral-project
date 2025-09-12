<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ShopDrawingStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $document;
    public $message;
    public $actionUrl;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Document $document
     * @return void
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->setMessageAndUrl();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail']; // Kirim ke database (in-app) dan email
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
                    ->subject('Pembaruan Status Shop Drawing: ' . $this->document->title)
                    ->greeting('Halo, ' . $notifiable->name . '!')
                    ->line($this->message)
                    ->action('Lihat Dokumen', $this->actionUrl)
                    ->line('Terima kasih telah menggunakan aplikasi M-Project.');
    }

    /**
     * Get the array representation of the notification.
     * (Untuk notifikasi in-app via database)
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'document_id' => $this->document->id,
        ];
    }
    
    /**
     * Helper untuk menentukan pesan dan URL berdasarkan status dokumen.
     */
    protected function setMessageAndUrl()
    {
        $documentTitle = Str::limit($this->document->title, 30);
        $packageId = $this->document->package_id;
        $this->actionUrl = route('documents.index', ['package' => $packageId]);

        switch ($this->document->status) {
            case 'menunggu_persetujuan_owner':
                $this->message = "Shop Drawing '{$documentTitle}' telah direview MK dan menunggu persetujuan Anda.";
                break;
            case 'revision':
                $this->message = "Shop Drawing '{$documentTitle}' membutuhkan revisi dari Anda.";
                break;
            case 'approved':
                $this->message = "Selamat! Shop Drawing '{$documentTitle}' telah disetujui sepenuhnya.";
                break;
            case 'rejected':
                $this->message = "Shop Drawing '{$documentTitle}' ditolak.";
                break;
            default:
                $this->message = "Ada pembaruan status pada Shop Drawing '{$documentTitle}'.";
                break;
        }
    }
}