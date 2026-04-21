@props([
    'days' => app(\App\Services\PortalAccessService::class)->workingDays(),
    'timeSlots' => app(\App\Services\PortalAccessService::class)->timeSlots(),
    'grid' => [],
    'showTeacher' => true,
    'showRoom' => true,
    'showSemester' => true,
    'colorBySubject' => false,
    'slotEditRoute' => null,
    'emptyText' => 'No timetable data available for this selection.',
])

<div class="relative bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden" x-data="{ scrollPos: 0 }">
    {{-- Mobile Scroll Indicator --}}
    <div class="lg:hidden absolute top-0 right-0 p-4 z-20 pointer-events-none opacity-40 animate-pulse">
        <i class="bi bi-arrow-right-circle text-slate-400"></i>
    </div>

    <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="sticky left-0 bg-slate-50 z-10 px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-r border-slate-200 w-32 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)]">
                        Timeline
                    </th>
                    @foreach($days as $day)
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-r border-slate-100 last:border-0 min-w-[200px]">
                            {{ $day }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($timeSlots as $timeSlot)
                    @php [$from, $to] = explode('-', $timeSlot); @endphp
                    <tr class="transition-colors hover:bg-slate-50/20">
                        <td class="sticky left-0 bg-white z-10 px-8 py-8 border-r border-slate-200 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-black text-slate-800 tracking-tight">{{ \Carbon\Carbon::parse($from)->format('H:i') }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($to)->format('H:i') }}</span>
                            </div>
                        </td>
                        @foreach($days as $day)
                            @php $slot = $grid[$day][$timeSlot] ?? null; @endphp
                            <td class="px-6 py-6 align-top border-r border-slate-50 last:border-0 group">
                                @if($slot)
                                    @php
                                        $palette = [
                                            'bg-indigo-50 border-indigo-100 text-indigo-700 ring-indigo-200',
                                            'bg-emerald-50 border-emerald-100 text-emerald-700 ring-emerald-200',
                                            'bg-amber-50 border-amber-100 text-amber-700 ring-amber-200',
                                            'bg-rose-50 border-rose-100 text-rose-700 ring-rose-200',
                                            'bg-cyan-50 border-cyan-100 text-cyan-700 ring-cyan-200',
                                            'bg-slate-100 border-slate-200 text-slate-700 ring-slate-300',
                                        ];
                                        $subjectId = (int) ($slot->subject->id ?? 0);
                                        $style = $palette[$subjectId % count($palette)];
                                        $innerClass = "rounded-2xl border p-5 h-full transition-all group-hover:shadow-lg group-hover:-translate-y-1 ring-offset-4 " . 
                                                     ($colorBySubject ? $style : 'bg-white border-slate-200 text-slate-800');
                                    @endphp
                                    
                                    @if($slotEditRoute)
                                        <a href="{{ route($slotEditRoute, $slot) }}" class="{{ $innerClass }} block">
                                            <div class="flex flex-col h-full justify-between gap-4">
                                                <div>
                                                    <p class="font-black text-sm leading-tight tracking-tight mb-2">{{ $slot->subject->name ?? 'Untitled Module' }}</p>
                                                    @if($showSemester)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-white/40 text-[9px] font-black uppercase tracking-widest border border-white/20">
                                                            Sem {{ $slot->subject?->semester_sequence ?? 'N/A' }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="space-y-2 pt-2 border-t border-black/5">
                                                    @if($showTeacher)
                                                        <div class="flex items-center gap-2 text-[10px] font-bold opacity-70">
                                                            <i class="bi bi-mortarboard text-xs"></i>
                                                            <span class="truncate">{{ $slot->teacher->user->name ?? 'Unassigned Faculty' }}</span>
                                                        </div>
                                                    @endif
                                                    @if($showRoom)
                                                        <div class="flex items-center gap-2 text-[10px] font-bold opacity-70">
                                                            <i class="bi bi-geo-alt text-xs"></i>
                                                            <span class="truncate">Room: {{ $slot->classroom->name ?? 'TBD' }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="{{ $innerClass }}">
                                            <div class="flex flex-col h-full justify-between gap-4">
                                                <div>
                                                    <p class="font-black text-sm leading-tight tracking-tight mb-2">{{ $slot->subject->name ?? 'Untitled Module' }}</p>
                                                    @if($showSemester)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-black/5 text-[9px] font-black uppercase tracking-widest border border-black/5">
                                                            Sem {{ $slot->subject?->semester_sequence ?? 'N/A' }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="space-y-2 pt-2 border-t border-slate-100">
                                                    @if($showTeacher)
                                                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                                                            <i class="bi bi-person text-indigo-500"></i>
                                                            <span class="truncate">{{ $slot->teacher->user->name ?? 'Unassigned Faculty' }}</span>
                                                        </div>
                                                    @endif
                                                    @if($showRoom)
                                                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                                                            <i class="bi bi-building text-indigo-500"></i>
                                                            <span class="truncate">{{ $slot->classroom->name ?? 'TBD' }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="h-32 border-2 border-dashed border-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-slate-50/50 transition-all">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-200 group-hover:text-slate-300">No Session</span>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($days) + 1 }}" class="text-center py-32 opacity-20">
                            <i class="bi bi-calendar-x text-6xl block mb-4"></i>
                            <p class="text-xs font-black uppercase tracking-widest">{{ $emptyText }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
