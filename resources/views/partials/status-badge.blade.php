@php $late = $status === \App\Models\Attendance::STATUS_LATE; @endphp
<span class="nb-badge {{ $late ? 'bg-tangerine' : 'bg-mint' }}">{{ $late ? 'Terlambat' : 'Hadir' }}</span>
