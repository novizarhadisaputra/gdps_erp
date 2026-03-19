<div class="space-y-8">
    {{-- New Comment Input --}}
    <div class="p-6 transition-all duration-300 border border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl rounded-2xl shadow-sm hover:shadow-md">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center w-10 h-10 font-semibold text-white bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl shadow-lg ring-4 ring-primary-500/10">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <textarea 
                    wire:model="body" 
                    placeholder="Write a comment or mention someone with @..."
                    class="block w-full px-4 py-3 text-gray-900 placeholder-gray-400 bg-transparent border-none rounded-xl focus:ring-2 focus:ring-primary-500/20 sm:text-sm min-h-[100px] resize-none"
                    onkeydown="if(event.ctrlKey && event.keyCode === 13) @this.postComment()"
                ></textarea>
                <div class="flex justify-between items-center pt-4 mt-2 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-[10px] text-gray-400">Ctrl + Enter to fast post</p>
                    <button 
                        wire:click="postComment"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 text-sm font-semibold text-white transition-all bg-primary-600 rounded-xl hover:bg-primary-700 hover:scale-[1.02] active:scale-[0.98] focus:ring-4 focus:ring-primary-500/20 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="postComment">Post Comment</span>
                        <span wire:loading wire:target="postComment">Posting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Comments Timeline --}}
    <div class="relative space-y-6">
        {{-- Progress Line --}}
        @if($comments->isNotEmpty())
            <div class="absolute left-9 top-0 bottom-0 w-px bg-gray-100 dark:bg-gray-800"></div>
        @endif

        @foreach($comments as $comment)
            <div wire:key="comment-{{ $comment->id }}" class="relative flex gap-6 group">
                {{-- Timeline Dot/Avatar --}}
                <div class="relative z-10 flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 font-medium text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm group-hover:border-primary-500 group-hover:text-primary-600 transition-colors">
                        {{ substr($comment->user->name, 0, 1) }}
                    </div>
                </div>

                <div class="flex-1 min-w-0 pt-1">
                    <div class="p-5 transition-all duration-300 border border-gray-100 dark:border-gray-800 bg-white/30 dark:bg-gray-900/30 backdrop-blur-md rounded-2xl group-hover:bg-white dark:group-hover:bg-gray-900 group-hover:shadow-lg group-hover:border-primary-500/20">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">• {{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            
                            @if($comment->user_id === auth()->id())
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editComment('{{ $comment->id }}')" class="p-2 text-gray-400 transition-colors hover:text-primary-600 hover:bg-primary-50 rounded-lg">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button 
                                        wire:confirm="Are you sure you want to delete this comment?"
                                        wire:click="deleteComment('{{ $comment->id }}')" 
                                        class="p-2 text-gray-400 transition-colors hover:text-danger-600 hover:bg-danger-50 rounded-lg"
                                    >
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($editingCommentId === $comment->id)
                            <div class="space-y-4">
                                <textarea 
                                    wire:model="editingBody"
                                    class="block w-full px-4 py-3 text-gray-900 bg-gray-50 dark:bg-gray-800/50 border-none rounded-xl focus:ring-2 focus:ring-primary-500/20 sm:text-sm min-h-[100px] resize-none"
                                ></textarea>
                                <div class="flex justify-end gap-2">
                                    <button wire:click="cancelEdit" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Cancel</button>
                                    <button wire:click="updateComment" class="px-5 py-2 text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-all">Save Changes</button>
                                </div>
                            </div>
                        @else
                                <div class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                {!! preg_replace('/@([a-zA-Z0-9._]+)/', '<span class="font-bold text-primary-600 dark:text-primary-400">@$1</span>', nl2br(e($comment->body))) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($comments->isEmpty())
            <div class="relative flex flex-col items-center justify-center py-12 text-center group">
                <div class="p-4 mb-4 bg-gray-50 dark:bg-gray-800/50 rounded-full group-hover:scale-110 transition-transform duration-500">
                   <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">No discussion yet</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Be the first to start a discussion about this record.</p>
            </div>
        @endif
    </div>
</div>
