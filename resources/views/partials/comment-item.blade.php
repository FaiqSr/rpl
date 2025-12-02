<div class="comment-item" data-comment-id="{{ $comment->comment_id }}" style="background: white; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); {{ $level > 0 ? 'margin-left: 3rem; border-left: 3px solid var(--primary-green);' : '' }}">
  <div class="d-flex gap-3">
    <div class="comment-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-green); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0;">
      {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
    </div>
    <div class="comment-content" style="flex: 1;">
      <div class="comment-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <div>
          <strong style="color: #2F2F2F; font-size: 0.9375rem;">{{ $comment->user->name ?? 'User' }}</strong>
          <small class="text-muted ms-2" style="font-size: 0.875rem;">{{ $comment->created_at->diffForHumans() }}</small>
        </div>
        @auth
        <div class="d-flex gap-2 align-items-center">
          @if(Auth::user()->user_id === $comment->user_id)
          <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteComment('{{ $comment->comment_id }}')" style="font-size: 0.75rem; text-decoration: none; border: none; background: none; padding: 0.25rem;">
            <i class="fa-solid fa-trash"></i>
          </button>
          @endif
          @if($level === 0)
          <button class="btn btn-sm btn-link text-primary p-0" onclick="showReplyForm('{{ $comment->comment_id }}')" style="font-size: 0.75rem; text-decoration: none; border: none; background: none;">
            <i class="fa-solid fa-reply me-1"></i> Balas
          </button>
          @endif
        </div>
        @endauth
      </div>
      <div class="comment-text" style="color: #2F2F2F; font-size: 0.9375rem; line-height: 1.6; margin-bottom: 0.75rem;">
        {{ $comment->content }}
      </div>
      
      <!-- Reply Form (hidden by default) -->
      @auth
      <div id="replyForm-{{ $comment->comment_id }}" class="reply-form" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f0f0f0;">
        <form onsubmit="submitReply(event, '{{ $comment->comment_id }}')">
          <textarea class="form-control" rows="2" placeholder="Tulis balasan..." style="border: 1px solid #e9ecef; border-radius: 8px; padding: 0.75rem; font-size: 0.875rem; resize: none;" maxlength="1000" required></textarea>
          <div class="d-flex justify-content-end gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideReplyForm('{{ $comment->comment_id }}')">Batal</button>
            <button type="submit" class="btn btn-sm btn-primary" style="background: var(--primary-green); border: none;">Kirim</button>
          </div>
        </form>
      </div>
      @endauth
      
      <!-- Replies -->
      @if($comment->replies->count() > 0)
        <div class="replies" style="margin-top: 1rem;">
          @foreach($comment->replies as $reply)
            @include('partials.comment-item', ['comment' => $reply, 'level' => $level + 1])
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>

