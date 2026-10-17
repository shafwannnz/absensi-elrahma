{{-- Thumbnail selfie absensi. Variabel: $attendance, $type ('in' = masuk, 'out' = pulang; default 'in') --}}
@php
    $type = $type ?? 'in';
    $hasPhoto = $type === 'out' ? $attendance->out_photo_path : $attendance->photo_path;
    $url = route('admin.attendances.photo', ['attendance' => $attendance, 'type' => $type]);
@endphp
@if ($hasPhoto)
    <a href="{{ $url }}" target="_blank" rel="noopener" title="Lihat foto penuh">
        <img src="{{ $url }}" alt="Selfie {{ $type === 'out' ? 'pulang' : 'masuk' }} {{ $attendance->user->name }}"
             loading="lazy" class="size-14 rounded-md border-[3px] border-black object-cover shadow-nb-sm">
    </a>
@else
    <span class="grid size-14 place-items-center rounded-md border-[3px] border-dashed border-black/40 text-center text-[10px] leading-tight font-bold text-zinc-500">
        {{ $type === 'out' && ! $attendance->checked_out_at ? 'Belum pulang' : 'Tanpa foto' }}
    </span>
@endif