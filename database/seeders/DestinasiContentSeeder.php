<?php

namespace Database\Seeders;

use App\Models\Destinasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DestinasiContentSeeder extends Seeder
{
    /**
     * Credits for the openly licensed source photographs bundled with this seeder.
     *
     * @var array<string, array{title: string, author: string, license: string, source: string}>
     */
    public const PHOTO_CREDITS = [
        'kota-bandung' => ['title' => 'Gedung Sate Bandung', 'author' => 'Gunawan Kartapranata', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Gedung_Sate_Bandung.jpg'],
        'kota-banjar' => ['title' => 'Banjar Jabar', 'author' => 'Devitapra', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Banjar_Jabar_-_panoramio.jpg'],
        'kota-bekasi' => ['title' => 'Patriot Bekasi Stadion', 'author' => 'Nodanodaboo', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Patriot_bekasi_stadion.jpg'],
        'kota-bogor' => ['title' => 'Istana Bogor', 'author' => 'Saipul bakrie', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Istana_Bogor_atau_Bogor_Palace.jpg'],
        'kota-cimahi' => ['title' => 'Stasiun Cimahi Tampak Depan', 'author' => 'A2613', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Stasiun_Cimahi_Depan.jpg'],
        'kota-cirebon' => ['title' => 'Keraton Kacirebonan', 'author' => '2025kfac', 'license' => 'CC0 1.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Keraton_Kacirebonan_5.jpg'],
        'kota-depok' => ['title' => 'Balai Kota Depok', 'author' => 'Pemerintah Kota Depok', 'license' => 'Public Domain (Indonesia)', 'source' => 'https://commons.wikimedia.org/wiki/File:Balai_Kota_Depok.jpg'],
        'kota-sukabumi' => ['title' => 'Sukabumi Train Station in July 2024', 'author' => 'Afrogindahood', 'license' => 'CC BY 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Sukabumi_Train_Station_in_July_2024.jpg'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->destinations() as $destination) {
                $slug = $destination['slug'];
                $photoPath = "destinasi/{$slug}.webp";
                $assetPath = database_path("seeders/assets/destinasi/{$slug}.webp");

                if (! File::isFile($assetPath)) {
                    throw new RuntimeException("Aset foto destinasi tidak ditemukan: {$assetPath}");
                }

                if (! Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->put($photoPath, File::get($assetPath));
                }

                Destinasi::query()->updateOrCreate(
                    ['slug' => $slug],
                    [...$destination, 'foto_utama' => $photoPath],
                );
            }
        });
    }

    /** @return array<int, array<string, bool|int|string>> */
    private function destinations(): array
    {
        return [
            [
                'nama' => 'Kota Bandung',
                'slug' => 'kota-bandung',
                'deskripsi_singkat' => 'Kota kreatif berhawa sejuk yang memadukan arsitektur bersejarah, kuliner, belanja, dan budaya Sunda.',
                'deskripsi_lengkap' => 'Kota Bandung merupakan ibu kota Provinsi Jawa Barat yang berada di kawasan Cekungan Bandung. Kota ini dikenal melalui bangunan bersejarah seperti Gedung Sate, koridor Asia Afrika dan Braga, ruang kreatif, ragam kuliner, serta kehidupan seni dan budaya Sunda. Bandung juga menjadi titik awal yang praktis untuk menjelajahi kawasan pegunungan di sekitarnya.',
                'lokasi' => 'Kota Bandung, Jawa Barat',
                'waktu_terbaik' => 'Mei–September saat curah hujan cenderung lebih rendah; datang pada hari kerja untuk menghindari kepadatan akhir pekan.',
                'cara_menuju' => 'Dapat dicapai dengan kereta antarkota menuju Stasiun Bandung, kereta cepat melalui Stasiun Padalarang atau Tegalluar yang tersambung angkutan pengumpan, serta kendaraan melalui jaringan jalan tol dari Jakarta dan kota-kota sekitarnya.',
                'transportasi_lokal' => 'Bus Trans Metro Bandung, angkot, kereta lokal Bandung Raya, taksi, transportasi daring, dan penyewaan kendaraan.',
                'tips' => 'Siapkan jaket ringan dan payung, periksa rekayasa lalu lintas saat akhir pekan, serta susun kunjungan berdasarkan kawasan agar waktu perjalanan lebih efisien.',
                'status' => true,
                'unggulan' => true,
                'urutan' => 10,
            ],
            [
                'nama' => 'Kota Banjar',
                'slug' => 'kota-banjar',
                'deskripsi_singkat' => 'Kota gerbang timur Jawa Barat dengan suasana tenang, bentang Sungai Citanduy, dan jalur perjalanan lintas provinsi.',
                'deskripsi_lengkap' => 'Kota Banjar berada di bagian timur Jawa Barat dan berperan sebagai salah satu gerbang menuju Jawa Tengah. Lanskap kotanya dipengaruhi Sungai Citanduy, kawasan perbukitan rendah, persawahan, dan koridor transportasi darat. Ritme kota yang relatif tenang menjadikannya persinggahan menarik untuk mengenal kehidupan lokal Priangan Timur.',
                'lokasi' => 'Kota Banjar, Jawa Barat',
                'waktu_terbaik' => 'Mei–September untuk perjalanan luar ruang dengan peluang hujan yang lebih rendah.',
                'cara_menuju' => 'Dapat dicapai dengan kereta antarkota yang berhenti di Stasiun Banjar, bus antarkota, atau kendaraan melalui jalur selatan Jawa Barat yang menghubungkan Ciamis dan wilayah Jawa Tengah.',
                'transportasi_lokal' => 'Angkutan kota, ojek, transportasi daring pada area layanan, becak di sejumlah kawasan, dan penyewaan kendaraan.',
                'tips' => 'Rencanakan jadwal kereta atau bus lebih awal, bawa pelindung matahari untuk kegiatan siang hari, dan siapkan uang tunai untuk transportasi lokal skala kecil.',
                'status' => true,
                'unggulan' => false,
                'urutan' => 20,
            ],
            [
                'nama' => 'Kota Bekasi',
                'slug' => 'kota-bekasi',
                'deskripsi_singkat' => 'Kota metropolitan di timur Jakarta dengan pusat kuliner, ruang publik, dan akses transportasi regional yang lengkap.',
                'deskripsi_lengkap' => 'Kota Bekasi merupakan bagian dari kawasan metropolitan Jabodetabek dan berkembang sebagai pusat permukiman, perdagangan, serta jasa. Selain pusat belanja dan kuliner, kota ini memiliki ruang publik, kawasan budaya, serta Stadion Patriot Candrabhaga yang menjadi salah satu penanda kota. Konektivitasnya memudahkan perjalanan singkat dari Jakarta maupun daerah lain di Jawa Barat.',
                'lokasi' => 'Kota Bekasi, Jawa Barat',
                'waktu_terbaik' => 'Mei–September; kegiatan luar ruang lebih nyaman dilakukan pada pagi atau sore hari.',
                'cara_menuju' => 'Terhubung dengan KRL Commuter Line, LRT Jabodebek, bus antarkota dan TransJakarta pada koridor tertentu, serta kendaraan melalui Jalan Tol Jakarta–Cikampek dan jaringan jalan metropolitan.',
                'transportasi_lokal' => 'Angkutan kota, bus perkotaan, KRL, LRT, taksi, transportasi daring, dan penyewaan kendaraan.',
                'tips' => 'Hindari jam sibuk pagi dan sore, gunakan kartu uang elektronik untuk angkutan massal, dan periksa prakiraan cuaca ketika mengunjungi ruang terbuka.',
                'status' => true,
                'unggulan' => false,
                'urutan' => 30,
            ],
            [
                'nama' => 'Kota Bogor',
                'slug' => 'kota-bogor',
                'deskripsi_singkat' => 'Kota hujan dengan kebun raya bersejarah, lanskap hijau, warisan kolonial, dan kekayaan kuliner.',
                'deskripsi_lengkap' => 'Kota Bogor terletak di selatan Jakarta dan dikenal sebagai Kota Hujan. Kebun Raya Bogor dan Istana Bogor membentuk pusat lanskap bersejarah kota, sementara museum, kampung tematik, dan sentra kulinernya menawarkan pengalaman yang beragam. Udara yang relatif sejuk dan akses yang mudah menjadikan Bogor tujuan perjalanan sehari maupun akhir pekan.',
                'lokasi' => 'Kota Bogor, Jawa Barat',
                'waktu_terbaik' => 'Mei–September, meskipun hujan tetap dapat turun sepanjang tahun; pagi hari ideal untuk menikmati kawasan hijau.',
                'cara_menuju' => 'Dapat dicapai dengan KRL Commuter Line menuju Stasiun Bogor, bus antarkota, atau kendaraan melalui Jalan Tol Jagorawi.',
                'transportasi_lokal' => 'Biskita Trans Pakuan, angkot, taksi, transportasi daring, dan berjalan kaki pada kawasan pusat kota tertentu.',
                'tips' => 'Selalu bawa payung atau jas hujan, mulai perjalanan pagi untuk menghindari kepadatan, dan gunakan angkutan umum saat berkeliling pusat kota.',
                'status' => true,
                'unggulan' => true,
                'urutan' => 40,
            ],
            [
                'nama' => 'Kota Cimahi',
                'slug' => 'kota-cimahi',
                'deskripsi_singkat' => 'Kota mungil di barat Bandung dengan jejak sejarah militer, bangunan cagar budaya, dan akses menuju perbukitan.',
                'deskripsi_lengkap' => 'Kota Cimahi berada di sebelah barat Kota Bandung dan menjadi bagian dari kawasan metropolitan Bandung Raya. Identitasnya kuat sebagai kota garnisun dengan sejumlah bangunan bersejarah, termasuk kawasan militer dan Stasiun Cimahi. Bagian utara kota menjadi penghubung menuju lanskap perbukitan, sementara pusat kotanya menawarkan kuliner dan kehidupan lokal yang mudah dijelajahi.',
                'lokasi' => 'Kota Cimahi, Jawa Barat',
                'waktu_terbaik' => 'Mei–September; pagi hari cocok untuk menjelajahi kawasan bersejarah dan area terbuka.',
                'cara_menuju' => 'Dapat dicapai dengan kereta lokal menuju Stasiun Cimahi, bus dan angkutan dari Bandung, atau kendaraan melalui akses Tol Baros dan jalan utama Bandung–Cimahi.',
                'transportasi_lokal' => 'Angkot, bus pada koridor Bandung Raya, kereta lokal, taksi, transportasi daring, dan penyewaan kendaraan.',
                'tips' => 'Perhatikan area dengan akses terbatas di sekitar fasilitas militer, hindari jam komuter, dan gabungkan kunjungan dengan destinasi Bandung Barat terdekat.',
                'status' => true,
                'unggulan' => false,
                'urutan' => 50,
            ],
            [
                'nama' => 'Kota Cirebon',
                'slug' => 'kota-cirebon',
                'deskripsi_singkat' => 'Kota pesisir bersejarah dengan keraton, batik megamendung, tradisi lintas budaya, dan kuliner khas.',
                'deskripsi_lengkap' => 'Kota Cirebon tumbuh sebagai kota pelabuhan dan pusat kebudayaan di pesisir utara Jawa Barat. Jejak Kesultanan Cirebon hadir melalui keraton, masjid tua, kampung batik, dan tradisi yang mempertemukan unsur Sunda, Jawa, Tionghoa, Arab, serta budaya pesisir. Empal gentong, nasi jamblang, dan tahu gejrot melengkapi pengalaman menjelajahi kota.',
                'lokasi' => 'Kota Cirebon, Jawa Barat',
                'waktu_terbaik' => 'Mei–September; mulai kegiatan pagi atau menjelang sore karena suhu pesisir dapat terasa panas pada siang hari.',
                'cara_menuju' => 'Terhubung dengan kereta antarkota menuju Stasiun Cirebon dan Cirebon Prujakan, bus antarkota, serta kendaraan melalui Jalan Tol Cikopo–Palimanan dan jaringan jalan pantai utara.',
                'transportasi_lokal' => 'Angkot, becak, taksi, transportasi daring, dan penyewaan kendaraan.',
                'tips' => 'Gunakan pakaian ringan dan pelindung matahari, periksa jam kunjungan keraton, serta siapkan waktu khusus untuk mencicipi kuliner di beberapa kawasan berbeda.',
                'status' => true,
                'unggulan' => true,
                'urutan' => 60,
            ],
            [
                'nama' => 'Kota Depok',
                'slug' => 'kota-depok',
                'deskripsi_singkat' => 'Kota penyangga Jakarta yang hidup dengan kampus, situ, ruang hijau, komunitas kreatif, dan ragam kuliner.',
                'deskripsi_lengkap' => 'Kota Depok berada di antara Jakarta dan Bogor serta menjadi bagian penting kawasan Jabodetabek. Kota ini dikenal sebagai kota pendidikan, terutama melalui kawasan Universitas Indonesia, sekaligus memiliki sejumlah situ, taman kota, kawasan heritage Depok Lama, dan pusat kuliner. Akses angkutan massal membuatnya mudah dijelajahi untuk perjalanan singkat.',
                'lokasi' => 'Kota Depok, Jawa Barat',
                'waktu_terbaik' => 'Mei–September; kunjungi situ dan taman pada pagi atau sore hari.',
                'cara_menuju' => 'Dapat dicapai dengan KRL Commuter Line melalui sejumlah stasiun di Depok, bus kawasan Jabodetabek, serta kendaraan melalui jaringan jalan utama dan jalan tol di sekitar kota.',
                'transportasi_lokal' => 'Angkot, KRL, bus, taksi, transportasi daring, dan penyewaan kendaraan.',
                'tips' => 'Hindari jam komuter, gunakan KRL untuk akses koridor utara–selatan, dan cek kondisi cuaca sebelum mengunjungi situ atau ruang terbuka.',
                'status' => true,
                'unggulan' => false,
                'urutan' => 70,
            ],
            [
                'nama' => 'Kota Sukabumi',
                'slug' => 'kota-sukabumi',
                'deskripsi_singkat' => 'Kota berhawa nyaman di kaki Gunung Gede–Pangrango dengan warisan sejarah, kuliner, dan akses menuju alam Sukabumi.',
                'deskripsi_lengkap' => 'Kota Sukabumi berada di kawasan selatan Jawa Barat pada jalur antara Bogor dan Cianjur. Pusat kotanya menyimpan bangunan bersejarah, taman, pasar, dan tradisi kuliner, sementara posisinya menjadi gerbang menuju bentang alam Sukabumi yang lebih luas. Skala kota yang bersahabat membuat kawasan pusatnya cukup nyaman untuk dijelajahi perlahan.',
                'lokasi' => 'Kota Sukabumi, Jawa Barat',
                'waktu_terbaik' => 'Mei–September; pagi hari menawarkan udara yang lebih segar untuk berjalan dan menjelajahi kota.',
                'cara_menuju' => 'Dapat dicapai dengan kereta Pangrango dari Bogor menuju Stasiun Sukabumi, bus antarkota, atau kendaraan melalui koridor Bogor–Sukabumi maupun Cianjur–Sukabumi.',
                'transportasi_lokal' => 'Angkot, ojek, transportasi daring, taksi lokal, dan penyewaan kendaraan.',
                'tips' => 'Pesan tiket kereta lebih awal pada akhir pekan, antisipasi kepadatan jalur darat, dan bawa jaket ringan untuk pagi atau malam hari.',
                'status' => true,
                'unggulan' => true,
                'urutan' => 80,
            ],
        ];
    }
}
