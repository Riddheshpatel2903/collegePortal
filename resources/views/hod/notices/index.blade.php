@extends('layouts.app')

@section('header_title', 'Department Notices')

@section('content')
    <div class="mb-4">
        <a href="{{ route('hod.notices.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded">Create Notice</a>
    </div>
    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead><tr><th>Title</th><th>Target</th><th>Priority</th><th>Expiry</th></tr></thead>
            <tbody>
            @forelse($notices as $notice)
                <tr>
                    <td>{{ $notice->title }}</td>
                    <td>{{ $notice->target_role }}</td>
                    <td>{{ ucfirst($notice->priority) }}</td>
                    <td>{{ $notice->expiry_date?->format('d M Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-6">No notices found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $notices->links() }}</div>
@endsection
