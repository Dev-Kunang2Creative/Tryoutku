/**
 * Lapisan hover untuk grafik garis di halaman statistik.
 *
 * Grafiknya sendiri sudah tergambar penuh di HTML, jadi berkas ini hanya
 * menambahkan pembacaan nilai saat kursor bergerak. Tanpa JavaScript, grafiknya
 * tetap utuh dan angkanya tetap terbaca lewat tabel di bawahnya.
 */
function pasangGrafik(wadah) {
    const id = wadah.dataset.grafik;
    const sumber = document.querySelector(`[data-grafik-data="${id}"]`);
    const tooltip = wadah.querySelector('[data-tooltip]');
    const silang = wadah.querySelector('[data-silang]');
    const svg = wadah.querySelector('svg');

    if (!sumber || !tooltip || !silang || !svg) {
        return;
    }

    let data;

    try {
        data = JSON.parse(sumber.textContent);
    } catch {
        return;
    }

    const tampilkan = (kolom) => {
        const indeks = Number(kolom.dataset.kolom);
        const baris = data[indeks];

        if (!baris) {
            return;
        }

        // Nama deret berasal dari judul paket yang diketik pengguna, jadi
        // selalu dimasukkan sebagai teks, tidak pernah sebagai markup.
        tooltip.replaceChildren();

        const judul = document.createElement('p');
        judul.className = 'text-xs font-medium text-slate-500';
        judul.textContent = baris.tanggal;
        tooltip.append(judul);

        baris.nilai.forEach((deret) => {
            const item = document.createElement('p');
            item.className = 'mt-1.5 flex items-center gap-2 text-sm';

            const kunci = document.createElement('span');
            kunci.style.cssText = `display:inline-block;width:12px;height:2px;background:${deret.warna}`;

            const angka = document.createElement('strong');
            angka.className = 'font-semibold text-slate-900 tabular';
            angka.textContent = deret.nilai === null ? '—' : deret.nilai;

            const nama = document.createElement('span');
            nama.className = 'min-w-0 truncate text-slate-500';
            nama.textContent = deret.nama;

            item.append(kunci, angka, nama);
            tooltip.append(item);
        });

        const kotak = kolom.getBoundingClientRect();
        const kotakWadah = wadah.getBoundingClientRect();
        const tengah = kotak.left - kotakWadah.left + kotak.width / 2;

        tooltip.classList.remove('hidden');
        tooltip.style.left = `${Math.min(Math.max(tengah - tooltip.offsetWidth / 2, 0), kotakWadah.width - tooltip.offsetWidth)}px`;
        tooltip.style.top = '0px';

        const x = Number(kolom.getAttribute('x')) + Number(kolom.getAttribute('width')) / 2;
        silang.setAttribute('x1', x);
        silang.setAttribute('x2', x);
        silang.setAttribute('opacity', '1');
    };

    const sembunyikan = () => {
        tooltip.classList.add('hidden');
        silang.setAttribute('opacity', '0');
    };

    svg.querySelectorAll('[data-kolom]').forEach((kolom) => {
        kolom.addEventListener('pointerenter', () => tampilkan(kolom));
        kolom.addEventListener('focus', () => tampilkan(kolom));
        kolom.addEventListener('blur', sembunyikan);
    });

    svg.addEventListener('pointerleave', sembunyikan);
}

document.querySelectorAll('[data-grafik]').forEach(pasangGrafik);
