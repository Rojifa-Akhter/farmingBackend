<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Investment;

class InvestmentStatusNotification extends Notification
{
    use Queueable;

    protected $investment;

    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toMail($notifiable)
    {

    }

    public function toArray($notifiable)
    {
        return [
            'investment_id' => $this->investment->id,
            'status' => $this->investment->invest_status,
            'amount' => $this->investment->amount,
            'message' => 'Your investment status has been updated to ' . ucfirst($this->investment->invest_status) . '.'
        ];
    }
}
