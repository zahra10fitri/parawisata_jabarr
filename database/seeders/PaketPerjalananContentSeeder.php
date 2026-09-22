<?php

namespace Database\Seeders;

use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PaketPerjalananContentSeeder extends Seeder
{
    /** @var array<string, array{title: string, author: string, license: string, source: string}> */
    public const PHOTO_CREDITS = [
        'pesona-bandung-klasik' => ['title' => 'Jalan Braga sign in Bandung', 'author' => 'MaheSwara', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Jalan_Braga_(Braga_Street)_sign_in_Bandung.jpg'],
        'danau-tenang-kota-banjar' => ['title' => 'Situ Mustika Kota Banjar', 'author' => 'wowo_s', 'license' => 'CC BY 3.0', 'source' => 'https://bernas.id/nasional/news/situ-mustika-kota-banjar-pesona-rekreasi-keluarga-di-tengah-hutan-jati'],
        'oase-alam-kota-bekasi' => ['title' => 'Curug Parigi', 'author' => 'Ezurasan', 'license' => 'CC0 1.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Curug_Parigi.jpg'],
        'bogor-botanical-discovery' => ['title' => 'Kebun Raya Bogor 02', 'author' => 'Cep Budhi Darma', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kebun_Raya_Bogor_02.jpg'],
        'jejak-budaya-cimahi' => ['title' => 'Kampung Adat Cireundeu - 01', 'author' => 'Pijri Paijar', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kampung_Adat_Cireundeu_-_01.jpg'],
        'warisan-keraton-cirebon' => ['title' => 'Keraton Kasepuhan, Cirebon', 'author' => 'Mokhammad Misdianto', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Keraton_Kasepuhan,_Cirebon_-_panoramio.jpg'],
        'ruang-tenang-depok' => ['title' => 'Menara Masjid Dian Al Mahri', 'author' => 'Alamnirvana', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Menara_Masjid_Dian_Al_Mahri.jpg'],
        'hangat-kota-sukabumi' => ['title' => 'Kawasan Wisata Cikundul', 'author' => 'Dokpim Kota Sukabumi', 'license' => 'Sumber pemerintah', 'source' => 'https://kdp.sukabumikota.go.id/2019/06/kawasan-cikundul-wisata-pemandian-air.html'],
        'lintas-warisan-jawa-barat' => ['title' => 'Bandung geologisch', 'author' => 'Rochelimit', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Bandung_geologisch.jpg'],
    ];

    public function run(): void
    {
        $destinations = Destinasi::query()->get()->keyBy('slug');
        $attractions = TempatWisata::query()->get()->keyBy('slug');

        DB::transaction(function () use ($destinations, $attractions): void {
            foreach ($this->packages() as $packageData) {
                $destinationSlugs = $packageData['destinasi'];
                $attractionSlugs = $packageData['tempat_wisata'];
                $itinerary = $packageData['itinerary'];
                unset($packageData['destinasi'], $packageData['tempat_wisata'], $packageData['itinerary']);

                $slug = $packageData['slug'];
                $photoPath = "paket-perjalanan/{$slug}.webp";
                $assetPath = database_path("seeders/assets/paket-perjalanan/{$slug}.webp");

                if (! File::isFile($assetPath)) {
                    throw new RuntimeException("Aset foto paket perjalanan tidak ditemukan: {$assetPath}");
                }

                if (! Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->put($photoPath, File::get($assetPath));
                }

                $package = PaketPerjalanan::query()->updateOrCreate(
                    ['slug' => $slug],
                    [...$packageData, 'foto_utama' => $photoPath],
                );

                $package->destinasi()->sync($this->idsForSlugs($destinations, $destinationSlugs, 'Destinasi'));
                $package->tempatWisata()->sync($this->idsForSlugs($attractions, $attractionSlugs, 'Tempat Wisata'));
                $this->syncItinerary($package, $itinerary);
            }
        });
    }

    /**
     * @param  Collection<string, Model>  $models
     * @param  array<int, string>  $slugs
     * @return array<int, int>
     */
    private function idsForSlugs($models, array $slugs, string $label): array
    {
        return collect($slugs)->map(function (string $slug) use ($models, $label): int {
            $model = $models->get($slug) ?? throw new RuntimeException("{$label} belum tersedia: {$slug}");

            return (int) $model->getKey();
        })->all();
    }

    /** @param  array<int, array<string, mixed>>  $days */
    private function syncItinerary(PaketPerjalanan $package, array $days): void
    {
        $keptDayIds = [];

        foreach ($days as $dayData) {
            $activities = $dayData['kegiatan'];
            unset($dayData['kegiatan']);
            $day = $package->itinerary()->updateOrCreate(['hari_ke' => $dayData['hari_ke']], $dayData);
            $keptDayIds[] = $day->id;
            $keptActivityIds = [];

            foreach ($activities as $activityData) {
                $activity = $day->kegiatan()->updateOrCreate(['urutan' => $activityData['urutan']], $activityData);
                $keptActivityIds[] = $activity->id;
            }

            $day->kegiatan()->whereNotIn('id', $keptActivityIds)->delete();
        }

        $package->itinerary()->whereNotIn('id', $keptDayIds)->delete();
    }

    /** @return array<int, array<string, mixed>> */
    private function packages(): array
    {
        return [
            $this->package(
                'Pesona Bandung Klasik', 'pesona-bandung-klasik', 1, 425000, true, 10,
                'Perjalanan sehari menyusuri wajah klasik Bandung melalui kisah bumi di Museum Geologi dan suasana ikonik Jalan Braga.',
                "Museum Geologi Bandung\nJalan kaki santai di koridor Braga\nPendamping perjalanan lokal\nWaktu bebas menikmati kuliner pusat kota",
                ['kota-bandung'], ['museum-geologi-bandung', 'jalan-braga'],
                [$this->day(1, 'Sains dan Warisan Kota Bandung', [
                    $this->activity('Bertemu di pusat Kota Bandung', 'Pengarahan singkat dan perkenalan rute perjalanan.', '08:00', 10),
                    $this->activity('Jelajah Museum Geologi Bandung', 'Mengenal koleksi batuan, fosil, sejarah bumi, dan geologi Indonesia.', '09:00', 20),
                    $this->activity('Istirahat dan makan siang', 'Makan siang di kawasan pusat kota sesuai rekomendasi pendamping.', '12:00', 30),
                    $this->activity('Menyusuri Jalan Braga', 'Berjalan santai mengamati arsitektur bersejarah dan ruang kreatif di koridor Braga.', '14:00', 40),
                    $this->activity('Perjalanan selesai', 'Program berakhir di kawasan Braga.', '17:00', 50),
                ])],
            ),
            $this->package(
                'Danau Tenang Kota Banjar', 'danau-tenang-kota-banjar', 1, 275000, false, 20,
                'Pelarian singkat menikmati dua ruang air dan lanskap hijau Kota Banjar dalam perjalanan santai tanpa terburu-buru.',
                "Situ Mustika dan Situ Leutik\nTransportasi lokal selama program\nPendamping perjalanan\nAir mineral",
                ['kota-banjar'], ['situ-mustika', 'situ-leutik'],
                [$this->day(1, 'Ruang Hijau dan Situ Kota Banjar', [
                    $this->activity('Penjemputan di Kota Banjar', 'Bertemu pendamping dan menuju kawasan Purwaharja.', '08:00', 10),
                    $this->activity('Menikmati Situ Mustika', 'Berjalan santai dan menikmati lanskap danau buatan di kawasan hutan jati.', '08:30', 20),
                    $this->activity('Makan siang lokal', 'Istirahat dan menikmati hidangan lokal Kota Banjar.', '12:00', 30),
                    $this->activity('Sore di Situ Leutik', 'Menjelajahi tepian situ dan ruang rekreasi hijau di Desa Cibeureum.', '14:00', 40),
                    $this->activity('Kembali ke pusat kota', 'Perjalanan berakhir di titik pertemuan awal.', '17:00', 50),
                ])],
            ),
            $this->package(
                'Oase Alam Kota Bekasi', 'oase-alam-kota-bekasi', 1, 295000, false, 30,
                'Sehari menemukan sisi hijau Kota Bekasi, dari Curug Parigi di Bantargebang hingga Hutan Kota Patriot di pusat kota.',
                "Curug Parigi\nHutan Kota Patriot Bina Bangsa\nTransportasi selama program\nPendamping perjalanan",
                ['kota-bekasi'], ['curug-parigi', 'hutan-kota-patriot'],
                [$this->day(1, 'Bentang Air dan Hutan Kota', [
                    $this->activity('Bertemu di Kota Bekasi', 'Pengarahan keselamatan dan pemeriksaan kondisi cuaca.', '07:30', 10),
                    $this->activity('Kunjungan Curug Parigi', 'Mengamati bentang air terjun dari area aman dan menikmati waktu fotografi.', '08:30', 20),
                    $this->activity('Makan siang', 'Istirahat di kawasan Kota Bekasi.', '12:00', 30),
                    $this->activity('Jelajah Hutan Kota Patriot', 'Berjalan santai di ruang hijau kota dan mengenal monumen perjuangan.', '14:00', 40),
                    $this->activity('Program selesai', 'Kembali ke titik pertemuan.', '16:30', 50),
                ])],
            ),
            $this->package(
                'Bogor Botanical Discovery', 'bogor-botanical-discovery', 1, 450000, true, 40,
                'Pengalaman edukatif sehari menjelajahi koleksi tumbuhan tropis Kebun Raya Bogor dan keragaman fauna Museum Zoologi.',
                "Tiket kawasan sesuai program\nKebun Raya Bogor\nMuseum Zoologi Bogor\nPendamping perjalanan\nMakan siang",
                ['kota-bogor'], ['kebun-raya-bogor', 'museum-zoologi-bogor'],
                [$this->day(1, 'Flora dan Fauna Nusantara', [
                    $this->activity('Bertemu di pintu Kebun Raya', 'Registrasi dan pengarahan rute berjalan kaki.', '08:00', 10),
                    $this->activity('Jelajah Kebun Raya Bogor', 'Menikmati koleksi tumbuhan, pepohonan tua, dan taman tematik.', '08:30', 20),
                    $this->activity('Makan siang dan istirahat', 'Beristirahat sebelum melanjutkan kunjungan museum.', '12:00', 30),
                    $this->activity('Museum Zoologi Bogor', 'Mengenal keanekaragaman fauna Indonesia melalui koleksi dan diorama.', '13:30', 40),
                    $this->activity('Program selesai', 'Kembali ke pintu keluar yang disepakati.', '16:00', 50),
                ])],
            ),
            $this->package(
                'Jejak Budaya Cimahi', 'jejak-budaya-cimahi', 1, 350000, true, 50,
                'Perjalanan budaya yang mempertemukan tradisi pangan Kampung Adat Cireundeu dengan jejak transportasi bersejarah Cimahi.',
                "Kampung Adat Cireundeu\nPendamping lokal\nTransportasi selama program\nMakan siang sederhana",
                ['kota-cimahi'], ['kampung-adat-cireundeu', 'stasiun-cimahi'],
                [$this->day(1, 'Tradisi dan Sejarah Kota Cimahi', [
                    $this->activity('Bertemu di Stasiun Cimahi', 'Mengenal singkat sejarah stasiun dari area publik sebelum melanjutkan perjalanan.', '08:00', 10),
                    $this->activity('Menuju Kampung Adat Cireundeu', 'Perjalanan menuju Leuwigajah dan perkenalan etika kunjungan.', '09:00', 20),
                    $this->activity('Belajar budaya Cireundeu', 'Mengenal tradisi masyarakat, ketahanan pangan singkong, dan lingkungan kampung.', '10:00', 30),
                    $this->activity('Makan siang dan interaksi lokal', 'Menikmati waktu istirahat serta berbincang bersama pendamping lokal.', '12:30', 40),
                    $this->activity('Kembali ke Stasiun Cimahi', 'Program selesai di titik pertemuan awal.', '15:30', 50),
                ])],
            ),
            $this->package(
                'Warisan Keraton Cirebon', 'warisan-keraton-cirebon', 2, 1350000, true, 60,
                'Dua hari mendalami warisan Kesultanan Cirebon melalui kompleks Keraton Kasepuhan dan Taman Air Gua Sunyaragi.',
                "Akomodasi 1 malam\nSarapan 1 kali dan makan siang 2 kali\nTransportasi lokal selama program\nPendamping perjalanan\nTiket objek sesuai itinerary",
                ['kota-cirebon'], ['keraton-kasepuhan', 'gua-sunyaragi'],
                [
                    $this->day(1, 'Keraton dan Cerita Pesisir', [
                        $this->activity('Penjemputan di Kota Cirebon', 'Bertemu pendamping di stasiun atau titik temu yang disepakati.', '09:00', 10),
                        $this->activity('Jelajah Keraton Kasepuhan', 'Mengenal sejarah kesultanan, arsitektur, dan koleksi keraton bersama pemandu.', '10:00', 20),
                        $this->activity('Makan siang khas Cirebon', 'Menikmati kuliner lokal sebelum menuju penginapan.', '13:00', 30),
                        $this->activity('Check-in dan waktu bebas', 'Beristirahat atau menjelajahi pusat kota secara mandiri.', '15:00', 40),
                    ]),
                    $this->day(2, 'Taman Air Sunyaragi', [
                        $this->activity('Sarapan dan check-out', 'Persiapan perjalanan hari kedua.', '07:30', 10),
                        $this->activity('Taman Air Gua Sunyaragi', 'Menjelajahi lorong, bangunan batu, dan kisah taman air Kesultanan Cirebon.', '09:00', 20),
                        $this->activity('Makan siang', 'Istirahat dan rangkuman perjalanan.', '12:00', 30),
                        $this->activity('Pengantaran kembali', 'Program selesai di stasiun atau titik akhir yang disepakati.', '14:00', 40),
                    ]),
                ],
            ),
            $this->package(
                'Ruang Tenang Depok', 'ruang-tenang-depok', 1, 325000, false, 70,
                'Perjalanan sehari yang memadukan wisata religi di Masjid Dian Al-Mahri dengan suasana santai Situ Pengasinan.',
                "Masjid Dian Al-Mahri\nSitu Pengasinan\nTransportasi selama program\nPendamping perjalanan",
                ['kota-depok'], ['masjid-kubah-emas', 'situ-pengasinan'],
                [$this->day(1, 'Arsitektur dan Lanskap Air Depok', [
                    $this->activity('Bertemu di Kota Depok', 'Pengarahan singkat dan perjalanan menuju Meruyung.', '08:00', 10),
                    $this->activity('Kunjungan Masjid Dian Al-Mahri', 'Menikmati arsitektur masjid dari area yang diizinkan tanpa mengganggu ibadah.', '09:00', 20),
                    $this->activity('Makan siang', 'Istirahat sebelum menuju kawasan Sawangan.', '12:00', 30),
                    $this->activity('Bersantai di Situ Pengasinan', 'Menikmati tepian situ dan suasana ruang terbuka pada sore hari.', '14:00', 40),
                    $this->activity('Program selesai', 'Kembali menuju titik pertemuan.', '17:00', 50),
                ])],
            ),
            $this->package(
                'Hangat Kota Sukabumi', 'hangat-kota-sukabumi', 2, 1100000, true, 80,
                'Dua hari menikmati ritme Kota Sukabumi, jejak Stasiun Sukabumi, dan relaksasi di kawasan air panas Cikundul.',
                "Akomodasi 1 malam\nSarapan 1 kali dan makan siang 2 kali\nTransportasi lokal\nPendamping perjalanan\nAkses objek sesuai itinerary",
                ['kota-sukabumi'], ['stasiun-sukabumi', 'pemandian-air-panas-cikundul'],
                [
                    $this->day(1, 'Tiba di Kota Sukabumi', [
                        $this->activity('Bertemu di Stasiun Sukabumi', 'Penjemputan dan pengenalan singkat sejarah stasiun dari area publik.', '10:00', 10),
                        $this->activity('Makan siang dan orientasi kota', 'Menikmati hidangan lokal dan mengenal pusat Kota Sukabumi.', '12:00', 20),
                        $this->activity('Check-in dan waktu bebas', 'Beristirahat di penginapan atau berjalan santai di pusat kota.', '15:00', 30),
                    ]),
                    $this->day(2, 'Relaksasi Cikundul', [
                        $this->activity('Sarapan dan check-out', 'Persiapan menuju Kecamatan Lembursitu.', '07:30', 10),
                        $this->activity('Pemandian Air Panas Cikundul', 'Menikmati kawasan air panas dengan tetap mengikuti ketentuan keselamatan.', '09:00', 20),
                        $this->activity('Makan siang', 'Istirahat sebelum kembali ke pusat kota.', '12:30', 30),
                        $this->activity('Pengantaran ke Stasiun Sukabumi', 'Program berakhir di stasiun.', '15:00', 40),
                    ]),
                ],
            ),
            $this->package(
                'Lintas Warisan Jawa Barat', 'lintas-warisan-jawa-barat', 3, 2950000, true, 90,
                'Tiga hari menghubungkan warisan ilmu dan kota Bandung, tradisi hidup Cimahi, serta jejak kesultanan di Cirebon.',
                "Akomodasi 2 malam\nSarapan 2 kali dan makan siang 3 kali\nTransportasi antarkota dan lokal selama program\nPendamping perjalanan\nTiket objek sesuai itinerary",
                ['kota-bandung', 'kota-cimahi', 'kota-cirebon'],
                ['museum-geologi-bandung', 'jalan-braga', 'kampung-adat-cireundeu', 'stasiun-cimahi', 'keraton-kasepuhan', 'gua-sunyaragi'],
                [
                    $this->day(1, 'Bandung: Bumi dan Kota Klasik', [
                        $this->activity('Museum Geologi Bandung', 'Memulai perjalanan dengan koleksi geologi dan sejarah bumi Indonesia.', '09:00', 10),
                        $this->activity('Makan siang', 'Istirahat di pusat Kota Bandung.', '12:00', 20),
                        $this->activity('Jalan Braga', 'Menyusuri koridor bersejarah dan menikmati waktu bebas.', '14:00', 30),
                        $this->activity('Check-in Bandung', 'Beristirahat di penginapan.', '17:00', 40),
                    ]),
                    $this->day(2, 'Cimahi dan Perjalanan ke Cirebon', [
                        $this->activity('Menuju Kampung Adat Cireundeu', 'Check-out lalu berangkat menuju Leuwigajah.', '08:00', 10),
                        $this->activity('Kampung Adat Cireundeu', 'Belajar tradisi masyarakat dan ketahanan pangan berbasis singkong.', '09:00', 20),
                        $this->activity('Singgah di Stasiun Cimahi', 'Mengenal jejak transportasi kota dari area publik.', '13:00', 30),
                        $this->activity('Perjalanan ke Cirebon', 'Melanjutkan perjalanan antarkota dan check-in setibanya di Cirebon.', '14:30', 40),
                    ]),
                    $this->day(3, 'Warisan Kesultanan Cirebon', [
                        $this->activity('Keraton Kasepuhan', 'Menjelajahi kompleks keraton dan sejarah budaya pesisir.', '08:30', 10),
                        $this->activity('Makan siang khas Cirebon', 'Istirahat sebelum kunjungan terakhir.', '12:00', 20),
                        $this->activity('Taman Air Gua Sunyaragi', 'Menutup perjalanan di kompleks taman air bersejarah.', '13:30', 30),
                        $this->activity('Pengantaran kembali', 'Program selesai di stasiun atau titik akhir Cirebon.', '16:00', 40),
                    ]),
                ],
            ),
        ];
    }

    /**
     * @param  array<int, string>  $destinations
     * @param  array<int, string>  $attractions
     * @param  array<int, array<string, mixed>>  $itinerary
     * @return array<string, mixed>
     */
    private function package(string $name, string $slug, int $duration, int $price, bool $featured, int $order, string $description, string $highlight, array $destinations, array $attractions, array $itinerary): array
    {
        $overnightInclusions = $duration > 1
            ? 'Akomodasi '.($duration - 1)." malam\nSarapan ".($duration - 1)." kali\n"
            : '';

        return [
            'nama' => $name,
            'slug' => $slug,
            'deskripsi' => $description,
            'durasi_hari' => $duration,
            'harga_mulai' => $price,
            'highlight' => $highlight,
            'termasuk' => $overnightInclusions."Transportasi selama program sesuai itinerary\nPendamping perjalanan\nTiket objek yang tercantum dalam itinerary\nMakan siang sesuai itinerary\nAir mineral",
            'tidak_termasuk' => "Transportasi menuju titik awal dan dari titik akhir\nPengeluaran pribadi dan aktivitas opsional\nAsuransi perjalanan\nBiaya akibat perubahan di luar kendali penyelenggara",
            'status' => true,
            'unggulan' => $featured,
            'urutan' => $order,
            'destinasi' => $destinations,
            'tempat_wisata' => $attractions,
            'itinerary' => $itinerary,
        ];
    }

    /**
     * @param  array<int, array<string, int|string|null>>  $activities
     * @return array<string, mixed>
     */
    private function day(int $day, string $title, array $activities): array
    {
        return ['hari_ke' => $day, 'judul' => $title, 'urutan' => ($day - 1) * 10, 'kegiatan' => $activities];
    }

    /** @return array<string, int|string|null> */
    private function activity(string $title, string $description, ?string $time, int $order): array
    {
        return ['judul' => $title, 'keterangan' => $description, 'waktu' => $time, 'urutan' => $order];
    }
}
