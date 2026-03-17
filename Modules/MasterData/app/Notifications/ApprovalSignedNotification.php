<?php

namespace Modules\MasterData\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class ApprovalSignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $record,
        protected string $message,
        protected string $url,
        protected ?string $title = 'Document Signed'
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $notification = \Filament\Notifications\Notification::make()
            ->title($this->title)
            ->body($this->message)
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('View Document')
                    ->url($this->url)
                    ->markAsRead(),
            ]);

        $data = $notification->getDatabaseMessage();
        $data['record_id'] = $this->record->id;
        $data['record_type'] = get_class($this->record);

        return $data;
    }
}
