<?php

namespace Modules\CRM\Livewire\Revision;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RevisionComments extends Component
{
    #[Locked]
    public Model $record;

    public string $body = '';

    public ?string $editingCommentId = null;

    public string $editingBody = '';

    protected $rules = [
        'body' => 'required|min:3',
        'editingBody' => 'required|min:3',
    ];

    public function mount(Model $record)
    {
        $this->record = $record;
    }

    public function postComment()
    {
        $this->validateOnly('body');

        $this->record->comments()->create([
            'body' => $this->body,
            'user_id' => Auth::id(),
        ]);

        $this->body = '';
        $this->dispatch('comment-posted');
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
    }

    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return;
        }

        $comment->delete();
    }

    public function cancelEdit()
    {
        $this->editingCommentId = null;
        $this->editingBody = '';
    }

    #[Computed]
    public function comments()
    {
        return $this->record->comments()
            ->with('user')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('crm::livewire.revision.revision-comments', [
            'comments' => $this->comments,
        ]);
    }
}
