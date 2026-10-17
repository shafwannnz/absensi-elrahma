{{-- Pesan sukses & daftar error validasi --}}
@if (session('success'))
    <div class="mb-5 rounded-md border-[3px] border-black bg-mint p-3 font-bold shadow-nb-sm" role="status">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-md border-[3px] border-black bg-bubble p-3 shadow-nb-sm" role="alert">
        <p class="font-bold">Periksa kembali isian Anda:</p>
        <ul class="mt-1 list-disc pl-5 text-sm font-medium">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
