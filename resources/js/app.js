import './bootstrap';

const kolomKode = document.querySelector('[data-kode-aktivasi]');
const kodeAktivasi = window.location.hash.slice(1);
if (kolomKode && /^[a-f0-9]{64}$/.test(kodeAktivasi)) {
    kolomKode.value = kodeAktivasi;
    window.history.replaceState(window.history.state, '', window.location.pathname + window.location.search);
}

document.querySelectorAll('[data-salin]').forEach((tombolSalin) => {
    const kolom = document.getElementById(tombolSalin.dataset.salin);
    const statusSalin = document.getElementById(`${tombolSalin.dataset.salin}-status`);
    tombolSalin.hidden = false;
    tombolSalin.addEventListener('click', async () => {
        kolom.focus();
        kolom.select();
        try {
            await navigator.clipboard.writeText(kolom.value);
            statusSalin.textContent = tombolSalin.dataset.salinSukses;
        } catch {
            statusSalin.textContent = 'Salin teks yang sudah dipilih.';
        }
    });
});

const tombolCetak = document.querySelector('[data-cetak-lowongan]');
if (tombolCetak) {
    tombolCetak.hidden = false;
    tombolCetak.addEventListener('click', () => window.print());
}
