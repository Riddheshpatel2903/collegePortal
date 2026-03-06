@extends('layouts.app')

@section('header_title', 'Academic Phase')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="glass-card p-6">
            <h2 class="text-2xl font-black text-slate-800">Academic Phase Control</h2>
            <p class="text-sm text-slate-500 mt-1">Only one phase can be active at a time. This drives semester calculation globally.</p>
        </div>

        <form method="POST" action="{{ route('admin.academic-phase.update') }}" class="glass-card p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="input-label">Active Phase</label>
                <select name="phase_name" class="input-premium" required>
                    @foreach($phases as $phase)
                        <option value="{{ $phase->phase_name }}" @selected($phase->is_active)>{{ $phase->phase_name }}</option>
                    @endforeach
                </select>
            </div>

            <button class="btn-primary-gradient">Update Phase</button>
        </form>

        <div class="glass-card overflow-hidden">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Phase</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($phases as $phase)
                        <tr>
                            <td>{{ $phase->phase_name }}</td>
                            <td>
                                @if($phase->is_active)
                                    <x-badge type="success">Active</x-badge>
                                @else
                                    <x-badge type="info">Inactive</x-badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
