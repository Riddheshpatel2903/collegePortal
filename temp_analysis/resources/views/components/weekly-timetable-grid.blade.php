@props([
    'days' => app(\App\Services\PortalAccessService::class)->workingDays(),
    'timeSlots' => app(\App\Services\PortalAccessService::class)->timeSlots(),
    'grid' => [],
    'showTeacher' => true,
    'showRoom' => true,
    'showSemester' => true,
    'colorBySubject' => false,
    'slotEditRoute' => null,
    'emptyText' => 'No timetable data.',
])

<div class="glass-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-white z-10">Time</th>
                    @foreach($days as $day)
                        <th>{{ ucfirst($day) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($timeSlots as $timeSlot)
                    @php [$from, $to] = explode('-', $timeSlot); @endphp
                    <tr>
                        <td class="sticky left-0 bg-white font-black whitespace-nowrap text-xs md:text-sm">{{ \Carbon\Carbon::parse($from)->format('H:i') }}<br class="hidden md:block">-<br class="hidden md:block">{{ \Carbon\Carbon::parse($to)->format('H:i') }}</td>
                        @foreach($days as $day)
                            @php $slot = $grid[$day][$timeSlot] ?? null; @endphp
                            <td class="align-top min-w-[110px] sm:min-w-[130px] lg:min-w-0">
                                @if($slot)
                                    @php
                                        $palette = [
                                            'bg-amber-50 border-amber-200',
                                            'bg-emerald-50 border-emerald-200',
                                            'bg-cyan-50 border-cyan-200',
                                            'bg-rose-50 border-rose-200',
                                            'bg-orange-50 border-orange-200',
                                            'bg-lime-50 border-lime-200',
                                        ];
                                        $subjectId = (int) ($slot->subject->id ?? 0);
                                        $tone = $palette[$subjectId % count($palette)];
                                    @endphp
                                    @php $innerClass = 'rounded-lg border p-2 ' . ($colorBySubject ? $tone : 'border-slate-200 bg-slate-50'); @endphp
                                    @if($slotEditRoute)
                                        <a href="{{ route($slotEditRoute, $slot) }}" class="{{ $innerClass }} block hover:ring-2 hover:ring-indigo-200 transition">
                                            <p class="font-bold text-xs leading-tight mb-1">{{ $slot->subject->name ?? 'N/A' }}</p>
                                            @if($showSemester)
                                                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Sem {{ $slot->semester?->semester_number ?? $slot->semester_number ?? $slot->subject?->semester_number ?? $slot->subject?->semester_sequence ?? 'N/A' }}</p>
                                            @endif
                                            @if($showTeacher)
                                                <p class="text-[10px] text-slate-500 font-medium leading-tight truncate" title="{{ $slot->teacher->user->name ?? 'N/A' }}">{{ $slot->teacher->user->name ?? 'N/A' }}</p>
                                            @endif
                                            @if($showRoom)
                                                <p class="text-[10px] text-slate-500 font-medium truncate" title="{{ $slot->classroom->name ?? 'N/A' }}">{{ $slot->classroom->name ?? 'N/A' }}</p>
                                            @endif
                                        </a>
                                    @else
                                        <div class="{{ $innerClass }} space-y-0.5">
                                            <p class="font-bold text-xs leading-tight">{{ $slot->subject->name ?? 'N/A' }}</p>
                                            @if($showSemester)
                                                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Sem {{ $slot->semester?->semester_number ?? $slot->semester_number ?? $slot->subject?->semester_number ?? $slot->subject?->semester_sequence ?? 'N/A' }}</p>
                                            @endif
                                            @if($showTeacher)
                                                <p class="text-[10px] text-slate-500 font-medium leading-tight line-clamp-2" title="{{ $slot->teacher->user->name ?? 'N/A' }}">{{ $slot->teacher->user->name ?? 'N/A' }}</p>
                                            @endif
                                            @if($showRoom)
                                                <p class="text-[10px] text-slate-500 font-medium truncate" title="{{ $slot->classroom->name ?? 'N/A' }}">{{ $slot->classroom->name ?? 'N/A' }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400">Free</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(2, count($days) + 1) }}" class="text-center py-8 text-slate-500">{{ $emptyText }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
