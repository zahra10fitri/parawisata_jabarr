<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TempatWisataContentSeeder extends Seeder
{
    /** @var array<string, array{title: string, author: string, license: string, source: string}> */
    public const PHOTO_CREDITS = [
        'museum-geologi-bandung' => ['title' => 'Bandung geologisch', 'author' => 'Rochelimit', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Bandung_geologisch.jpg'],
        'jalan-braga' => ['title' => 'Jalan Braga sign in Bandung', 'author' => 'MaheSwara', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Jalan_Braga_(Braga_Street)_sign_in_Bandung.jpg'],
        'situ-mustika' => ['title' => 'Situ Mustika Kota Banjar', 'author' => 'wowo_s', 'license' => 'CC BY 3.0', 'source' => 'https://bernas.id/nasional/news/situ-mustika-kota-banjar-pesona-rekreasi-keluarga-di-tengah-hutan-jati'],
        'situ-leutik' => ['title' => 'Situ Leutik', 'author' => 'Kementerian Pariwisata Republik Indonesia', 'license' => 'Sumber pemerintah', 'source' => 'https://sisparnas.kemenpar.go.id/p/74294'],
        'curug-parigi' => ['title' => 'Curug Parigi', 'author' => 'Ezurasan', 'license' => 'CC0 1.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Curug_Parigi.jpg'],
        'hutan-kota-patriot' => ['title' => 'Hutan Kota Patriot', 'author' => 'Diskominfostandi Kota Bekasi', 'license' => 'Sumber pemerintah', 'source' => 'https://diskominfo.bekasikota.go.id/content/berita-terkini/3118/hutan-kota-patriot-sebagai-paruparu-kota-bekas'],
        'kebun-raya-bogor' => ['title' => 'Kebun Raya Bogor 02', 'author' => 'Cep Budhi Darma', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kebun_Raya_Bogor_02.jpg'],
        'museum-zoologi-bogor' => ['title' => 'Bogor Zoology Museum front', 'author' => 'RXerself, Creutzen', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Bogor_Zoology_Museum_front.jpg'],
        'kampung-adat-cireundeu' => ['title' => 'Kampung Adat Cireundeu - 01', 'author' => 'Pijri Paijar', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kampung_Adat_Cireundeu_-_01.jpg'],
        'stasiun-cimahi' => ['title' => 'Stasiun Cimahi Depan', 'author' => 'A2613', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Stasiun_Cimahi_Depan.jpg'],
        'keraton-kasepuhan' => ['title' => 'Keraton Kasepuhan, Cirebon', 'author' => 'Mokhammad Misdianto', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Keraton_Kasepuhan,_Cirebon_-_panoramio.jpg'],
        'gua-sunyaragi' => ['title' => 'Gua Sunyaragi Cirebon 01', 'author' => 'Jie73', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Gua_Sunyaragi_Cirebon_01.jpg'],
        'masjid-kubah-emas' => ['title' => 'Menara Masjid Dian Al Mahri', 'author' => 'Alamnirvana', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Menara_Masjid_Dian_Al_Mahri.jpg'],
        'situ-pengasinan' => ['title' => 'Situ Pengasinan Sawangan Depok', 'author' => 'Ardy Suckier', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Situ_Pengasinan_Sawangan_Depok.jpg'],
        'pemandian-air-panas-cikundul' => ['title' => 'Kawasan Wisata Cikundul', 'author' => 'Dokpim Kota Sukabumi', 'license' => 'Sumber pemerintah', 'source' => 'https://kdp.sukabumikota.go.id/2019/06/kawasan-cikundul-wisata-pemandian-air.html'],
        'stasiun-sukabumi' => ['title' => 'Sukabumi Train Station in July 2024', 'author' => 'Afrogindahood', 'license' => 'CC BY 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Sukabumi_Train_Station_in_July_2024.jpg'],
    ];

    public function run(): void
    {
        $placesByDestination = $this->places();
        $destinations = Destinasi::query()->whereIn('slug', array_keys($placesByDestination))->get()->keyBy('slug');
        $experiences = Pengalaman::query()->get()->keyBy('slug');
        $activities = Aktivitas::query()->get()->keyBy('slug');
        $facilities = Fasilitas::query()->get()->keyBy('slug');

        DB::transaction(function () use ($placesByDestination, $destinations, $experiences, $activities, $facilities): void {
            foreach ($placesByDestination as $destinationSlug => $places) {
                $destination = $destinations->get($destinationSlug)
                    ?? throw new RuntimeException("Destinasi belum tersedia: {$destinationSlug}");

                foreach ($places as $place) {
                    $relations = $place['relations'];
                    unset($place['relations']);
                    $slug = $place['slug'];
                    $photoPath = "tempat-wisata/{$slug}.webp";
                    $assetPath = database_path("seeders/assets/tempat-wisata/{$slug}.webp");

                    if (! File::isFile($assetPath)) {
                        throw new RuntimeException("Aset foto tempat wisata tidak ditemukan: {$assetPath}");
                    }

                    if (! Storage::disk('public')->exists($photoPath)) {
                        Storage::disk('public')->put($photoPath, File::get($assetPath));
                    }

                    $touristAttraction = TempatWisata::query()->updateOrCreate(
                        ['slug' => $slug],
                        [...$place, 'destinasi_id' => $destination->id, 'foto_utama' => $photoPath],
                    );

                    $touristAttraction->pengalaman()->sync($this->relationIds($experiences, $relations['pengalaman'], 'Pengalaman'));
                    $touristAttraction->aktivitas()->sync($this->relationIds($activities, $relations['aktivitas'], 'Aktivitas'));
                    $touristAttraction->fasilitas()->sync($this->relationIds($facilities, $relations['fasilitas'], 'Fasilitas'));
                }
            }
        });
    }

    /**
     * @param  Collection<string, Model>  $models
     * @param  array<int, string>  $slugs
     * @return array<int, int>
     */
    private function relationIds(Collection $models, array $slugs, string $label): array
    {
        return collect($slugs)->map(function (string $slug) use ($models, $label): int {
            $model = $models->get($slug) ?? throw new RuntimeException("{$label} belum tersedia: {$slug}");

            return (int) $model->getKey();
        })->all();
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    private function places(): array
    {
        return [
            'kota-bandung' => [
                $this->place('Museum Geologi Bandung', 'museum-geologi-bandung', 'Museum ilmu kebumian bersejarah dengan koleksi batuan, mineral, fosil, dan informasi geologi Indonesia.', 'Museum Geologi menempati bangunan bergaya Art Deco yang diresmikan pada 1929. Koleksinya mengajak pengunjung memahami sejarah bumi, sumber daya geologi, fosil vertebrata, serta dinamika kebencanaan di Indonesia.', 'Jalan Diponegoro No. 57, Cihaur Geulis, Cibeunying Kaler, Kota Bandung', 'Pagi hingga siang pada hari kerja untuk suasana yang lebih leluasa.', 'Periksa jadwal kunjungan resmi sebelum datang; beberapa ruang pamer dapat memiliki pengaturan akses khusus.', true, 10, ['budaya', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
                $this->place('Jalan Braga', 'jalan-braga', 'Koridor bersejarah Kota Bandung dengan arsitektur kolonial, galeri, kafe, dan suasana kota yang khas.', 'Jalan Braga merupakan salah satu koridor bersejarah paling dikenal di pusat Kota Bandung. Deretan bangunan lama, ruang seni, tempat makan, dan kedekatannya dengan kawasan Asia Afrika menjadikannya nyaman dijelajahi dengan berjalan kaki.', 'Jalan Braga, Kecamatan Sumur Bandung, Kota Bandung', 'Pagi untuk menikmati arsitektur atau sore hingga malam untuk suasana kawasan yang lebih hidup.', 'Gunakan transportasi umum atau parkir di titik resmi sekitar kawasan; trotoar dapat ramai pada akhir pekan.', true, 20, ['budaya', 'kuliner'], ['fotografi'], ['area-parkir', 'toilet', 'tempat-makan']),
            ],
            'kota-banjar' => [
                $this->place('Situ Mustika', 'situ-mustika', 'Danau buatan di kawasan hutan jati yang dikembangkan sebagai ruang rekreasi alam Kota Banjar.', 'Situ Mustika berada di Kecamatan Purwaharja dan dikelilingi lanskap hutan jati. Dokumen Pemerintah Kota Banjar mencatat situ ini sebagai danau buatan yang dikembangkan menjadi objek wisata.', 'Kecamatan Purwaharja, Kota Banjar', 'Pagi atau sore pada musim kemarau untuk kegiatan luar ruang yang lebih nyaman.', 'Bawa air minum dan pelindung matahari serta cek kondisi akses setempat sebelum berangkat.', false, 30, ['alam', 'keluarga', 'hidden-gem'], ['fotografi'], ['area-parkir']),
                $this->place('Situ Leutik', 'situ-leutik', 'Embung dan ruang rekreasi hijau di Desa Cibeureum dengan lintasan jalan kaki serta suasana teduh.', 'Situ Leutik merupakan danau buatan yang dikelola Pemerintah Kota Banjar dan dikembangkan sebagai objek wisata. Kawasannya dimanfaatkan warga untuk berjalan santai, berolahraga, memancing, dan menikmati ruang terbuka.', 'Desa Cibeureum, Kecamatan Banjar, Kota Banjar', 'Pagi atau sore, terutama ketika cuaca cerah.', 'Jaga kebersihan area situ, awasi anak-anak di tepi air, dan pastikan kembali kondisi fasilitas sebelum berkunjung.', true, 40, ['alam', 'keluarga', 'hidden-gem'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
            ],
            'kota-bekasi' => [
                $this->place('Curug Parigi', 'curug-parigi', 'Air terjun melebar di aliran Kali Bekasi yang berada di wilayah Bantargebang, Kota Bekasi.', 'Curug Parigi terbentuk pada bentang batu di aliran Kali Bekasi dan dikenal karena bentuk air terjunnya yang melebar. Lokasinya berada di Kecamatan Bantargebang dalam wilayah administratif Kota Bekasi, bukan Kabupaten Bekasi.', 'Kelurahan Cikiwul, Kecamatan Bantargebang, Kota Bekasi', 'Pagi pada musim kemarau; hindari datang ketika debit sungai tinggi atau setelah hujan deras.', 'Amati kondisi debit dan cuaca, jangan turun ke area licin, serta patuhi pembatas keselamatan di lokasi.', true, 50, ['alam', 'hidden-gem'], ['fotografi'], ['area-parkir']),
                $this->place('Hutan Kota Patriot Bina Bangsa', 'hutan-kota-patriot', 'Ruang hijau kota dengan pepohonan rindang, lintasan joging, area bermain, dan monumen perjuangan.', 'Hutan Kota Patriot Bina Bangsa merupakan taman kota di pusat Kota Bekasi. Pemerintah Kota Bekasi mencatat kawasan ini memiliki ribuan pohon, lintasan joging, tempat bermain anak, toilet, musala, dan Tugu Perjuangan Rakyat Bekasi.', 'Jalan Ahmad Yani No. 2, Kayuringin Jaya, Bekasi Selatan, Kota Bekasi', 'Pagi atau sore saat udara lebih sejuk.', 'Gunakan fasilitas dengan tertib, jaga kebersihan, dan hindari jam terpanas untuk aktivitas luar ruang.', false, 60, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
            ],
            'kota-bogor' => [
                $this->place('Kebun Raya Bogor', 'kebun-raya-bogor', 'Kebun botani bersejarah di pusat Kota Bogor dengan koleksi tumbuhan tropis dan lanskap hijau yang luas.', 'Kebun Raya Bogor merupakan pusat konservasi tumbuhan ex situ dan kebun botani bersejarah di jantung Kota Bogor. Jalur pejalan kaki, pepohonan tua, taman tematik, serta kedekatannya dengan Istana Bogor menjadikannya tujuan edukasi dan rekreasi.', 'Jalan Ir. H. Juanda No. 13, Paledang, Bogor Tengah, Kota Bogor', 'Pagi pada hari kerja agar lebih teduh dan tidak terlalu ramai.', 'Kenakan alas kaki nyaman, bawa payung atau jas hujan, dan cek ketentuan tiket serta pintu masuk resmi sebelum datang.', true, 70, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala', 'tempat-makan']),
                $this->place('Museum Zoologi Bogor', 'museum-zoologi-bogor', 'Museum koleksi fauna Indonesia yang berada di dalam kompleks Kebun Raya Bogor.', 'Museum Zoologi Bogor menyimpan koleksi ilmiah dan diorama fauna yang membantu pengunjung mengenal keanekaragaman hayati Indonesia. Bangunannya berada di kompleks Kebun Raya Bogor dan menjadi tujuan edukatif yang cocok dipadukan dalam satu kunjungan.', 'Jalan Ir. H. Juanda No. 9, Paledang, Bogor Tengah, Kota Bogor', 'Pagi hingga siang pada hari kerja.', 'Pastikan akses dan jadwal museum saat membeli tiket Kebun Raya; ikuti larangan menyentuh koleksi.', false, 80, ['budaya', 'keluarga'], ['fotografi'], ['toilet', 'musala', 'tempat-makan']),
            ],
            'kota-cimahi' => [
                $this->place('Kampung Adat Cireundeu', 'kampung-adat-cireundeu', 'Kampung adat Sunda di Leuwigajah yang mempertahankan tradisi, ketahanan pangan singkong, dan kehidupan komunal.', 'Kampung Adat Cireundeu dikenal melalui tradisi masyarakat Sunda Wiwitan dan pola pangan berbasis singkong yang diwariskan lintas generasi. Kunjungan memberi ruang untuk belajar mengenai budaya, lingkungan, dan kehidupan masyarakat secara langsung.', 'Kelurahan Leuwigajah, Kecamatan Cimahi Selatan, Kota Cimahi', 'Pagi hingga sore dengan kunjungan yang telah dikoordinasikan bersama pengelola atau warga.', 'Hormati aturan adat, berpakaian sopan, minta izin sebelum memotret warga atau kegiatan, dan gunakan pemandu lokal bila tersedia.', true, 90, ['budaya', 'kuliner'], ['fotografi'], ['area-parkir', 'toilet']),
                $this->place('Stasiun Cimahi', 'stasiun-cimahi', 'Stasiun kereta bersejarah yang menjadi salah satu penanda perkembangan Kota Cimahi sejak masa kolonial.', 'Stasiun Cimahi melayani perjalanan kereta dan memiliki nilai sejarah dalam perkembangan kota garnisun Cimahi. Fasad bangunan lama dapat diamati dari area publik tanpa mengganggu fungsi utama stasiun sebagai simpul transportasi aktif.', 'Jalan Stasiun, Baros, Kecamatan Cimahi Tengah, Kota Cimahi', 'Pagi atau sore ketika arus penumpang tidak terlalu padat.', 'Nikmati arsitektur hanya dari area yang diizinkan, jangan memasuki peron tanpa tiket, dan prioritaskan pergerakan penumpang.', false, 100, ['budaya'], ['fotografi'], ['area-parkir', 'toilet', 'musala', 'tempat-makan']),
            ],
            'kota-cirebon' => [
                $this->place('Keraton Kasepuhan', 'keraton-kasepuhan', 'Kompleks keraton bersejarah yang merekam perjalanan Kesultanan Cirebon dan percampuran budaya pesisir.', 'Keraton Kasepuhan adalah salah satu pusat warisan budaya utama Kota Cirebon. Kompleksnya memuat bangunan keraton, halaman berlapis, benda pusaka, dan ragam ornamen yang memperlihatkan pengaruh Sunda, Jawa, Islam, Tionghoa, dan Eropa.', 'Jalan Kasepuhan No. 43, Kesepuhan, Lemahwungkuk, Kota Cirebon', 'Pagi hingga siang pada hari kerja.', 'Kenakan pakaian sopan, gunakan pemandu untuk memahami konteks sejarah, dan pastikan izin sebelum memotret koleksi tertentu.', true, 110, ['budaya', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
                $this->place('Taman Air Gua Sunyaragi', 'gua-sunyaragi', 'Kompleks cagar budaya berupa taman air, gua buatan, dan bangunan bata karang peninggalan Kesultanan Cirebon.', 'Taman Air Gua Sunyaragi merupakan kompleks bersejarah dengan lorong, bangunan batu, dan bekas taman air. Situs ini dahulu berkaitan dengan kegiatan peristirahatan dan spiritual keluarga keraton serta kini menjadi salah satu ikon wisata budaya Kota Cirebon.', 'Jalan Brigjen Dharsono, Sunyaragi, Kesambi, Kota Cirebon', 'Pagi atau sore agar lebih nyaman menjelajahi area terbuka.', 'Gunakan alas kaki antiselip, jangan memanjat bagian cagar budaya, dan lindungi diri dari panas matahari.', true, 120, ['budaya', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
            ],
            'kota-depok' => [
                $this->place('Masjid Dian Al-Mahri', 'masjid-kubah-emas', 'Kompleks masjid di Meruyung yang dikenal luas melalui kubah berwarna emas dan arsitekturnya yang monumental.', 'Masjid Dian Al-Mahri, yang populer sebagai Masjid Kubah Emas, berada di Kecamatan Limo, Kota Depok. Selain berfungsi sebagai tempat ibadah, kompleksnya dikunjungi untuk wisata religi dan menikmati detail arsitektur masjid.', 'Jalan Raya Meruyung, Meruyung, Kecamatan Limo, Kota Depok', 'Di luar waktu salat utama agar kunjungan tidak mengganggu kegiatan ibadah.', 'Berpakaian sopan, patuhi pemisahan area ibadah, jaga ketenangan, dan periksa aturan kunjungan terbaru dari pengelola.', true, 130, ['budaya', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
                $this->place('Situ Pengasinan', 'situ-pengasinan', 'Danau kota di Sawangan yang dimanfaatkan sebagai ruang rekreasi warga dan kegiatan di tepi air.', 'Situ Pengasinan merupakan salah satu situ di wilayah administratif Kota Depok. Lanskap air dan ruang terbukanya menjadi tempat warga bersantai serta menikmati suasana yang berbeda dari kepadatan kawasan perkotaan.', 'Kelurahan Pengasinan, Kecamatan Sawangan, Kota Depok', 'Pagi atau sore pada cuaca cerah.', 'Awasi anak-anak di dekat air, jangan membuang sampah ke situ, dan cek kondisi fasilitas maupun kegiatan air sebelum berkunjung.', false, 140, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'tempat-makan']),
            ],
            'kota-sukabumi' => [
                $this->place('Pemandian Air Panas Cikundul', 'pemandian-air-panas-cikundul', 'Pemandian air panas alami di tepi Sungai Cimandiri, kawasan Lembursitu, Kota Sukabumi.', 'Pemandian Air Panas Cikundul merupakan objek wisata yang telah lama dikenal di Kota Sukabumi. Lokasinya berada di kawasan Cikundul dekat aliran Sungai Cimandiri dan memiliki kolam serta ruang berendam yang memanfaatkan sumber air panas alami.', 'Kelurahan Cikundul, Kecamatan Lembursitu, Kota Sukabumi', 'Pagi atau sore; hindari waktu hujan lebat karena lokasinya berdekatan dengan sungai.', 'Periksa kondisi operasional sebelum datang, ikuti batas waktu berendam, dan berhati-hati di permukaan basah.', true, 150, ['alam', 'keluarga', 'hidden-gem'], ['fotografi'], ['area-parkir', 'toilet', 'musala', 'tempat-makan']),
                $this->place('Stasiun Sukabumi', 'stasiun-sukabumi', 'Stasiun kereta bersejarah di pusat Kota Sukabumi yang masih aktif melayani perjalanan penumpang.', 'Stasiun Sukabumi merupakan simpul transportasi penting sekaligus bagian dari jejak perkembangan kota. Bangunan dan lingkungan relnya dapat diapresiasi dari area publik sambil tetap menghormati fungsi stasiun yang aktif.', 'Jalan Stasiun Barat, Gunungparang, Kecamatan Cikole, Kota Sukabumi', 'Pagi atau sore di luar puncak keberangkatan kereta.', 'Jangan masuk ke area terbatas atau rel, miliki tiket jika menuju peron, dan jangan menghambat arus penumpang saat mengambil foto.', false, 160, ['budaya'], ['fotografi'], ['area-parkir', 'toilet', 'musala', 'tempat-makan']),
            ],
        ];
    }

    /**
     * @param  array<int, string>  $experiences
     * @param  array<int, string>  $activities
     * @param  array<int, string>  $facilities
     * @return array<string, mixed>
     */
    private function place(string $name, string $slug, string $summary, string $description, string $location, string $bestTime, string $tips, bool $featured, int $order, array $experiences, array $activities, array $facilities): array
    {
        return [
            'nama' => $name,
            'slug' => $slug,
            'deskripsi_singkat' => $summary,
            'deskripsi_lengkap' => $description,
            'lokasi' => $location,
            'jam_operasional' => null,
            'harga_tiket' => null,
            'waktu_terbaik' => $bestTime,
            'tips' => $tips,
            'status' => true,
            'unggulan' => $featured,
            'urutan' => $order,
            'relations' => ['pengalaman' => $experiences, 'aktivitas' => $activities, 'fasilitas' => $facilities],
        ];
    }
}
