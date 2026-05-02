<?php

namespace Modules\Project\Livewire\Comments;

use App\Models\Comment;
use App\Models\User;
use Filament\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Project\Models\DailyReport;
use Modules\Project\Models\Project;
use Modules\Project\Models\ProjectTask;

class CommentsTimeline extends Component
{
    public Model $record;

    public string $body = '';

    public ?string $editingCommentId = null;

    public string $editingBody = '';

    protected $rules = [
        'body' => 'required|min:2',
        'editingBody' => 'required|min:2',
    ];

    public function mount(Model $record)
    {
        $this->record = $record;
    }

    public function postComment()
    {
        $this->validateOnly('body');

        $comment = $this->record->comments()->create([
            'body' => $this->body,
            'user_id' => Auth::id(),
        ]);

        $this->body = '';

        Notification::make()
            ->title('Comment posted')
            ->success()
            ->send();

        $this->dispatch('comment-posted');

        // Potential for mention parsing and notification logic here
        $this->handleMentions($comment);
    }

    protected function handleMentions(Comment $comment)
    {
        // Regex to find @mentions (e.g., @john.doe or @JohnDoe)
        preg_match_all('/@([a-zA-Z0-9._]+)/', $comment->body, $matches);

        if (empty($matches[1])) {
            return;
        }

        $mentionedUsernames = array_unique($matches[1]);

        foreach ($mentionedUsernames as $username) {
            // Try to find user by email prefix or name (stripped spaces)
            $user = User::where('email', 'like', "{$username}@%")
                ->orWhereRaw("REPLACE(name, ' ', '') COLLATE utf8mb4_unicode_ci LIKE ?", ["%{$username}%"])
                ->first();

            if ($user && $user->id !== Auth::id()) {
                Notification::make()
                    ->title('You were mentioned in a comment')
                    ->body(Auth::user()->name.' mentioned you in '.$this->record->getTable())
                    ->actions([
                        NotificationAction::make('view')
                            ->button()
                            ->url($this->getNotificationUrl()),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }

    protected function getNotificationUrl(): string
    {
        // Dynamic URL generation based on model type
        $resource = match (get_class($this->record)) {
            Project::class => 'projects',
            ProjectTask::class => 'project-tasks',
            DailyReport::class => 'daily-reports',
            default => 'projects',
        };

        // This is a bit tricky for nested resources, but we can try to build it
        return "/admin/{$resource}/{$this->record->id}";
    }

    public function editComment($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return;
        }

        $this->editingCommentId = $id;
        $this->editingBody = $comment->body;
    }

    public function updateComment()
    {
        $this->validateOnly('editingBody');

        $comment = Comment::findOrFail($this->editingCommentId);

        if ($comment->user_id !== Auth::id()) {
            return;
        }

        $comment->update([
            'body' => $this->editingBody,
        ]);

        $this->cancelEdit();

        Notification::make()
            ->title('Comment updated')
            ->success()
            ->send();
    }

    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return;
        }

        $comment->delete();

        Notification::make()
            ->title('Comment deleted')
            ->success()
            ->send();
    }

    public function cancelEdit()
    {
        $this->editingCommentId = null;
        $this->editingBody = '';
    }

    public function getCommentsProperty()
    {
        return $this->record->comments()
            ->with('user')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('project::livewire.comments.comments-timeline', [
            'comments' => $this->comments,
        ]);
    }
}
