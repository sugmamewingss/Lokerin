{{-- Panel identitas di halaman masuk/daftar --}}
<div class="relative hidden overflow-hidden bg-brand-950 p-10 text-white lg:flex lg:flex-col">
    <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 22px 22px;"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-brand-500/30 blur-3xl"></div>
    <x-logo class="relative h-11 w-11" />
    <h2 class="relative mt-auto text-3xl font-extrabold leading-tight text-white">{{ $judulPanel }}</h2>
    <ul class="relative mt-6 space-y-3 text-sm text-brand-100">
        @foreach ($poinPanel as $poin)
            <li class="flex items-start gap-3"><x-ikon nama="centang" class="h-5 w-5 shrink-0 text-brand-300" />{{ $poin }}</li>
        @endforeach
    </ul>
</div>
