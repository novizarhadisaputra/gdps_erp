<?php

namespace Modules\MasterData\Notifications;

use Filament\Support\Icons\Heroicon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class ApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $record,
        protected string $message,
        protected string $url,
        protected ?string $title = 'Approval Required'
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $notification = \Filament\Notifications\Notification::make()
            ->title(__($this->title))
            ->body($this->message)
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('info')
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label(__('View Document'))
                    ->url($this->url)
                    ->markAsRead(),
            ]);

        $data = $notification->getDatabaseMessage();
        $data['record_id'] = $this->record->id;
        $data['record_type'] = get_class($this->record);

        return $data;
    }
}
