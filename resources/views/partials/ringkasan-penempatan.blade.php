<dl class="mt-3 grid gap-3 rounded-lg bg-slate-50 p-3 text-sm sm:grid-cols-3">
    <div>
        <dt class="text-slate-600">Mulai kerja</dt>
        <dd class="font-medium">{{ $penempatan->tanggal_mulai?->format('d/m/Y') ?? 'Belum tercatat' }}</dd>
    </div>
    <div>
        <dt class="text-slate-600">Jenis kontrak</dt>
        <dd class="font-medium">{{ \App\Models\Penempatan::JENIS_KONTRAK[$penempatan->jenis_kontrak] ?? 'Belum tercatat' }}</dd>
    </div>
    <div>
        <dt class="text-slate-600">Status BPJS yang dicatat usaha</dt>
        <dd class="font-medium">{{ $penempatan->terdaftar_bpjs ? 'Terdaftar' : 'Belum terdaftar' }}</dd>
    </div>
</dl>
