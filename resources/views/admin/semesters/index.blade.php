@extends('layouts.app')

@section('header_title', 'Manage Semesters')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Semesters</h2>
            <p class="text-sm text-slate-400 mt-1">Manage academic semesters for each course.</p>
        </div>
        <a href="{{ route('admin.semesters.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-plus-lg"></i> Add Semester
        </a>
    </div>

    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Session</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semesters as $sem)
                    <tr class="group">
                        <td><span class="text-sm font-bold text-violet-600">{{ $loop->iteration }}</span></td>
                        <td><span class="text-sm font-semibold text-slate-700">{{ $sem->name }}</span></td>
                        <td><span class="text-sm text-slate-500">{{ $sem->course->name ?? 'N/A' }}</span></td>
                        <td><span class="text-sm text-slate-500">{{ $sem->academicSession->name ?? 'N/A' }}</span></td>
                        <td>
                            <span class="text-xs text-slate-500">
                                {{ optional($sem->start_date)->format('d M Y') ?? 'N/A' }}
                                -
                                {{ optional($sem->end_date)->format('d M Y') ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs font-semibold uppercase tracking-widest px-2 py-1 rounded-full
                                                {{ $sem->status === 'active' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                                {{ $sem->status === 'completed' ? 'bg-slate-100 text-slate-600' : '' }}
                                                {{ $sem->status === 'upcoming' ? 'bg-amber-50 text-amber-700' : '' }}">
                                {{ ucfirst($sem->status ?? 'upcoming') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('admin.semesters.destroy', $sem->id) }}" class="inline"
                                onsubmit="return confirm('Delete this semester?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition-colors">
                                    <i class="bi bi-trash3"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-sm text-slate-400 py-8">No semesters found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 mx-auto">
            {{ $semesters->links() }}
        </div>
    </div>
@endsection