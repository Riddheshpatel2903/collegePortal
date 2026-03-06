<div class="flex justify-end gap-2">
    <a href="{{ route('admin.users.edit', $user->id) }}"
        class="h-8 w-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center hover:bg-violet-600 hover:text-white transition-all text-sm">
        <i class="bi bi-pencil-fill"></i>
    </a>
    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline"
        onsubmit="return confirm('Delete this user?')">
        @csrf
        @method('DELETE')
        <button type="submit"
            class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all text-sm">
            <i class="bi bi-trash3-fill"></i>
        </button>
    </form>
</div>