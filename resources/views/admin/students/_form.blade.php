{{-- Form bersama untuk tambah & ubah mahasiswa. Variabel: $student (opsional) --}}
@php $s = $student ?? null; @endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="nb-card space-y-4 p-4 sm:p-5">
        <h3 class="text-xl">Data mahasiswa</h3>

        <div>
            <label for="name" class="nb-label">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name', $s?->name) }}" class="nb-input" required>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="nim" class="nb-label">NIM</label>
                <input id="nim" name="nim" value="{{ old('nim', $s?->nim) }}" inputmode="numeric" class="nb-input" required>
            </div>
            <div>
                <label for="phone" class="nb-label">No. HP</label>
                <input id="phone" name="phone" value="{{ old('phone', $s?->phone) }}" inputmode="tel" class="nb-input">
            </div>
        </div>
        <div>
            <label for="email" class="nb-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $s?->email) }}" class="nb-input" required>
        </div>
        <div>
            <label for="study_program" class="nb-label">Program studi</label>
            <input id="study_program" name="study_program" value="{{ old('study_program', $s?->study_program) }}" class="nb-input" placeholder="mis. Teknik Informatika">
        </div>
        <div>
            <label for="password" class="nb-label">Kata sandi {{ $s ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input id="password" name="password" type="password" minlength="8" class="nb-input" autocomplete="new-password" {{ $s ? '' : 'required' }}>
            <p class="mt-1 text-sm text-zinc-600">Minimal 8 karakter.</p>
        </div>
        <label class="flex items-center gap-2 font-bold">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-5 accent-black" @checked(old('is_active', $s?->is_active ?? true))>
            Akun aktif
        </label>
    </div>

    <div class="space-y-3">
        @include('partials.face-capture', ['hasFace' => $s?->hasFace() ?? false])
        <p class="text-sm font-medium">Pendaftaran wajah opsional di sini. Jika dilewati, mahasiswa dapat mendaftarkan wajahnya sendiri saat login pertama.</p>
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="nb-btn bg-mint">Simpan</button>
    <a href="{{ route('admin.students.index') }}" class="nb-btn bg-white">Batal</a>
</div>

@push('scripts')
    <script defer src="{{ config('absensi.face.script_url') }}"></script>
    @vite('resources/js/pages/enroll.js')
@endpush
