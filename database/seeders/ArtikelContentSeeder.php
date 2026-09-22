<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\KategoriArtikel;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ArtikelContentSeeder extends Seeder
{
    /** @var array<string, array{title: string, author: string, license: string, source: string}> */
    public const PHOTO_CREDITS = [
        'membaca-bandung-dari-bumi-ke-braga' => ['title' => 'Bandung geologisch', 'author' => 'Rochelimit', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Bandung_geologisch.jpg'],
        'sehari-belajar-alam-di-bogor' => ['title' => 'Kebun Raya Bogor 02', 'author' => 'Cep Budhi Darma', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kebun_Raya_Bogor_02.jpg'],
        'singkong-dan-keteguhan-cireundeu' => ['title' => 'Kampung Adat Cireundeu - 01', 'author' => 'Pijri Paijar', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kampung_Adat_Cireundeu_-_01.jpg'],
        'jejak-kesultanan-di-cirebon' => ['title' => 'Keraton Kasepuhan, Cirebon', 'author' => 'Mokhammad Misdianto', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Keraton_Kasepuhan,_Cirebon_-_panoramio.jpg'],
        'dua-situ-kota-banjar' => ['title' => 'Situ Leutik', 'author' => 'Kementerian Pariwisata Republik Indonesia', 'license' => 'Sumber pemerintah', 'source' => 'https://sisparnas.kemenpar.go.id/p/74294'],
        'menemukan-sisi-hijau-bekasi' => ['title' => 'Hutan Kota Patriot', 'author' => 'Diskominfostandi Kota Bekasi', 'license' => 'Sumber pemerintah', 'source' => 'https://diskominfo.bekasikota.go.id/content/berita-terkini/3118/hutan-kota-patriot-sebagai-paruparu-kota-bekas'],
        'etika-mengunjungi-ruang-budaya' => ['title' => 'Gua Sunyaragi Cirebon 01', 'author' => 'Jie73', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Gua_Sunyaragi_Cirebon_01.jpg'],
        'pangan-singkong-cireundeu' => ['title' => 'Kampung Adat Cireundeu - 01', 'author' => 'Pijri Paijar', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Kampung_Adat_Cireundeu_-_01.jpg'],
        'dua-hari-santai-sukabumi' => ['title' => 'Kawasan Wisata Cikundul', 'author' => 'Dokpim Kota Sukabumi', 'license' => 'Sumber pemerintah', 'source' => 'https://kdp.sukabumikota.go.id/2019/06/kawasan-cikundul-wisata-pemandian-air.html'],
        'panduan-wisata-tenang-depok' => ['title' => 'Menara Masjid Dian Al Mahri', 'author' => 'Alamnirvana', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Menara_Masjid_Dian_Al_Mahri.jpg'],
        'tips-museum-bersama-keluarga' => ['title' => 'Bogor Zoology Museum front', 'author' => 'RXerself, Creutzen', 'license' => 'CC BY-SA 4.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Bogor_Zoology_Museum_front.jpg'],
        'rasa-pesisir-cirebon' => ['title' => 'Keraton Kasepuhan, Cirebon', 'author' => 'Mokhammad Misdianto', 'license' => 'CC BY-SA 3.0', 'source' => 'https://commons.wikimedia.org/wiki/File:Keraton_Kasepuhan,_Cirebon_-_panoramio.jpg'],
    ];

    public function run(): void
    {
        $this->call(KategoriArtikelSeeder::class);

        $categories = KategoriArtikel::query()->get()->keyBy('slug');
        $destinations = Destinasi::query()->get()->keyBy('slug');
        $attractions = TempatWisata::query()->get()->keyBy('slug');

        DB::transaction(function () use ($categories, $destinations, $attractions): void {
            foreach ($this->articles() as $articleData) {
                $categorySlug = $articleData['kategori'];
                $destinationSlugs = $articleData['destinasi'];
                $attractionSlugs = $articleData['tempat_wisata'];
                unset($articleData['kategori'], $articleData['destinasi'], $articleData['tempat_wisata']);

                $category = $categories->get($categorySlug)
                    ?? throw new RuntimeException("Kategori artikel belum tersedia: {$categorySlug}");
                $slug = $articleData['slug'];
                $photoPath = "artikel/{$slug}.webp";
                $assetPath = database_path("seeders/assets/artikel/{$slug}.webp");

                if (! File::isFile($assetPath)) {
                    throw new RuntimeException("Aset foto artikel tidak ditemukan: {$assetPath}");
                }

                if (! Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->put($photoPath, File::get($assetPath));
                }

                $article = Artikel::query()->updateOrCreate(
                    ['slug' => $slug],
                    [...$articleData, 'kategori_artikel_id' => $category->id, 'foto_utama' => $photoPath],
                );

                $article->destinasi()->sync($this->idsForSlugs($destinations, $destinationSlugs, 'Destinasi'));
                $article->tempatWisata()->sync($this->idsForSlugs($attractions, $attractionSlugs, 'Tempat Wisata'));
            }
        });
    }

    /**
     * @param  Collection<string, Model>  $models
     * @param  array<int, string>  $slugs
     * @return array<int, int>
     */
    private function idsForSlugs(Collection $models, array $slugs, string $label): array
    {
        return collect($slugs)->map(function (string $slug) use ($models, $label): int {
            $model = $models->get($slug) ?? throw new RuntimeException("{$label} belum tersedia: {$slug}");

            return (int) $model->getKey();
        })->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function articles(): array
    {
        return [
            $this->article(
                'Membaca Bandung dari Bumi hingga Braga', 'membaca-bandung-dari-bumi-ke-braga', 'panduan-perjalanan',
                'Panduan menyusun satu hari di Bandung melalui dua wajah kota: pengetahuan kebumian dan koridor bersejarah Braga.',
                <<<'CONTENT'
Bandung menyimpan cerita dalam lapisan yang berbeda. Sebagian dapat dibaca melalui batuan dan fosil, sebagian lagi melalui fasad bangunan serta ritme jalan kota. Museum Geologi Bandung dan Jalan Braga menawarkan dua pintu masuk yang saling melengkapi untuk memahami keduanya.

MULAI DARI CERITA BUMI

Museum Geologi cocok menjadi pembuka perjalanan karena koleksinya memberi konteks tentang bentang alam Indonesia, sumber daya geologi, fosil, dan kebencanaan. Luangkan waktu untuk membaca penjelasan koleksi, bukan sekadar berpindah dari satu ruang ke ruang lain. Dengan begitu, kunjungan terasa seperti perjalanan menelusuri waktu.

BERJALAN DI KORIDOR BRAGA

Setelah istirahat siang, arahkan perjalanan ke pusat kota dan susuri Jalan Braga dengan berjalan kaki. Perhatikan proporsi bangunan, detail pintu dan jendela, serta hubungan Braga dengan kawasan Asia Afrika. Koridor ini tetap menjadi ruang kota yang aktif, jadi pengalaman terbaik datang ketika kita berjalan perlahan tanpa menghalangi aktivitas warga.

SUSUN PERJALANAN BERDASARKAN KAWASAN

Kedua lokasi berada di pusat Kota Bandung, tetapi lalu lintas dapat memengaruhi waktu tempuh. Gunakan transportasi umum atau kendaraan yang sama sepanjang program, siapkan pelindung hujan, dan periksa informasi kunjungan resmi sebelum berangkat. Hindari memasukkan terlalu banyak titik agar masih ada waktu untuk menikmati kota.
CONTENT,
                true, '2026-09-18 08:00:00', ['kota-bandung'], ['museum-geologi-bandung', 'jalan-braga'],
            ),
            $this->article(
                'Sehari Belajar Alam di Jantung Kota Bogor', 'sehari-belajar-alam-di-bogor', 'itinerary',
                'Rencana perjalanan santai yang menggabungkan Kebun Raya Bogor dan Museum Zoologi tanpa mengejar terlalu banyak tempat.',
                <<<'CONTENT'
Kebun Raya Bogor dan Museum Zoologi berada dalam satu kawasan yang memungkinkan perjalanan edukatif dilakukan dengan berjalan kaki. Kombinasi ini cocok untuk keluarga, pelajar, maupun siapa saja yang ingin mengenal flora dan fauna Indonesia dalam satu hari.

PAGI: MENYUSURI KEBUN RAYA

Datanglah pada pagi hari ketika udara masih nyaman. Tentukan beberapa taman atau koleksi yang paling ingin dilihat, lalu ikuti jalur yang tersedia. Kebun raya sangat luas; memilih rute lebih baik daripada berusaha melihat semuanya sekaligus. Berhenti sejenak di bawah pepohonan juga menjadi bagian penting dari pengalaman.

SIANG: ISTIRAHAT DENGAN CUKUP

Sisihkan waktu makan dan minum sebelum memasuki museum. Bogor dikenal dengan hujan yang dapat datang tiba-tiba, sehingga payung atau jas hujan ringan tetap berguna. Alas kaki yang nyaman akan membuat perjalanan jauh lebih menyenangkan.

SORE: MENGENAL FAUNA DI MUSEUM ZOOLOGI

Museum Zoologi memperluas cerita dari tumbuhan menuju keragaman satwa. Baca label koleksi dan ajak anak mencatat satu hal baru dari setiap bagian. Sebelum berkunjung, pastikan kembali akses museum serta ketentuan kawasan melalui kanal resmi pengelola karena pengaturan dapat berubah.
CONTENT,
                true, '2026-09-15 09:00:00', ['kota-bogor'], ['kebun-raya-bogor', 'museum-zoologi-bogor'],
            ),
            $this->article(
                'Singkong dan Keteguhan Kampung Adat Cireundeu', 'singkong-dan-keteguhan-cireundeu', 'budaya',
                'Mengenal bagaimana pangan, tradisi, dan hubungan dengan lingkungan membentuk identitas masyarakat Cireundeu.',
                <<<'CONTENT'
Kampung Adat Cireundeu di Leuwigajah, Kota Cimahi, dikenal karena masyarakatnya mempertahankan tradisi dan pola pangan berbasis singkong. Di sini, makanan bukan sekadar sajian, melainkan bagian dari pengetahuan yang diwariskan dan cara komunitas menjaga kemandirian.

PANGAN SEBAGAI PENGETAHUAN

Pengolahan singkong membutuhkan pemahaman tentang bahan, tahapan kerja, dan penyimpanan. Pengetahuan tersebut tumbuh melalui praktik sehari-hari dan diteruskan antargenerasi. Karena itu, kunjungan ke Cireundeu sebaiknya dipandang sebagai kesempatan belajar, bukan sekadar mencari latar foto.

TRADISI YANG HIDUP

Cireundeu bukan museum tanpa penghuni. Ia adalah ruang hidup dengan kegiatan warga, nilai adat, dan tata krama yang perlu dihormati. Dengarkan penjelasan pendamping lokal, minta izin sebelum memotret orang atau kegiatan, dan hindari menyederhanakan tradisi hanya menjadi hal yang dianggap unik.

DATANG DENGAN SIKAP YANG TEPAT

Koordinasikan kunjungan terlebih dahulu bila memungkinkan. Kenakan pakaian sopan, ikuti arahan warga, dan sisihkan waktu untuk berdialog. Perjalanan yang baik meninggalkan penghargaan lebih besar terhadap masyarakat sekaligus manfaat yang wajar bagi ekonomi lokal.
CONTENT,
                true, '2026-09-12 08:30:00', ['kota-cimahi'], ['kampung-adat-cireundeu'],
            ),
            $this->article(
                'Jejak Kesultanan dalam Ruang Kota Cirebon', 'jejak-kesultanan-di-cirebon', 'budaya',
                'Membaca sejarah Cirebon melalui halaman Keraton Kasepuhan dan susunan batu Taman Air Gua Sunyaragi.',
                <<<'CONTENT'
Cirebon tumbuh sebagai kota pesisir yang mempertemukan beragam jalur budaya. Jejak pertemuan itu dapat diamati di Keraton Kasepuhan dan Taman Air Gua Sunyaragi, dua situs yang menghadirkan cerita kesultanan melalui bentuk ruang yang berbeda.

KERATON SEBAGAI PUSAT INGATAN

Keraton Kasepuhan menyimpan halaman, bangunan, ornamen, dan koleksi yang membantu pengunjung memahami perjalanan Kesultanan Cirebon. Pengaruh Jawa, Sunda, Islam, Tionghoa, dan Eropa dapat dibaca pada berbagai detail. Pemandu lokal akan membantu menempatkan benda dan ruang dalam konteks yang lebih utuh.

SUNYARAGI DAN GAGASAN TAMAN AIR

Gua Sunyaragi merupakan kompleks taman air dengan lorong dan bangunan batu yang khas. Menjelajahinya memerlukan alas kaki yang nyaman serta kehati-hatian, sebab beberapa permukaan tidak rata. Jangan memanjat bagian situs atau menyentuh elemen yang dilindungi.

MERAWAT WARISAN MELALUI CARA BERKUNJUNG

Datanglah dengan pakaian sopan, ikuti jalur pengunjung, dan patuhi aturan fotografi. Sisakan waktu yang cukup di setiap tempat agar perjalanan tidak berubah menjadi daftar singgah. Mengunjungi situs budaya dengan perlahan adalah salah satu cara sederhana untuk menghargai warisan yang dijaga lintas generasi.
CONTENT,
                true, '2026-09-09 10:00:00', ['kota-cirebon'], ['keraton-kasepuhan', 'gua-sunyaragi'],
            ),
            $this->article(
                'Dua Situ untuk Menikmati Ritme Pelan Kota Banjar', 'dua-situ-kota-banjar', 'hidden-gem',
                'Situ Mustika dan Situ Leutik menawarkan perjalanan ringan di antara hutan jati, tepian air, dan ruang rekreasi warga.',
                <<<'CONTENT'
Kota Banjar sering dikenal sebagai gerbang timur Jawa Barat, tetapi kota ini juga memiliki ruang air yang layak dinikmati tanpa agenda padat. Situ Mustika dan Situ Leutik merupakan danau buatan yang kemudian dikembangkan sebagai kawasan rekreasi.

PAGI DI SITU MUSTIKA

Situ Mustika berada di Kecamatan Purwaharja dan memiliki suasana hijau yang dipengaruhi kawasan hutan jati di sekitarnya. Pagi hari memberi cahaya yang lebih lembut dan udara yang nyaman untuk berjalan santai. Nikmati lanskap dari area yang aman dan jangan meninggalkan sampah.

SORE DI SITU LEUTIK

Situ Leutik berada di Desa Cibeureum dan menjadi ruang rekreasi bagi warga. Tepian air, pepohonan, serta jalur di sekitarnya cocok untuk perjalanan santai. Kondisi air dan fasilitas dapat berubah menurut musim, sehingga pengunjung sebaiknya memeriksa keadaan terbaru sebelum datang.

PERJALANAN YANG TIDAK TERBURU-BURU

Kedua situ lebih tepat dinikmati sebagai ruang jeda daripada lokasi dengan daftar atraksi panjang. Bawa air minum, pelindung matahari, dan uang tunai secukupnya. Hormati aktivitas warga serta fungsi lingkungan situ sebagai bagian dari sistem air kota.
CONTENT,
                false, '2026-09-06 07:30:00', ['kota-banjar'], ['situ-mustika', 'situ-leutik'],
            ),
            $this->article(
                'Menemukan Sisi Hijau Kota Bekasi', 'menemukan-sisi-hijau-bekasi', 'hidden-gem',
                'Dari bentang air Curug Parigi hingga rindangnya Hutan Kota Patriot, Bekasi memiliki ruang alam di tengah lanskap urban.',
                <<<'CONTENT'
Kota Bekasi identik dengan mobilitas metropolitan, tetapi dua tempat memperlihatkan sisi yang berbeda. Curug Parigi menghadirkan bentang air di Bantargebang, sedangkan Hutan Kota Patriot Bina Bangsa menjadi ruang hijau di pusat kota.

CURUG PARIGI DAN KEKUATAN ALIRAN AIR

Curug Parigi terbentuk pada aliran Kali Bekasi dan memiliki bentuk melebar di atas bentang batu. Keindahannya harus dinikmati dengan kesadaran terhadap keselamatan. Debit air dapat berubah, permukaan batu bisa licin, dan area sungai bukan tempat untuk mengambil risiko demi foto.

HUTAN KOTA UNTUK BERHENTI SEJENAK

Hutan Kota Patriot memiliki pepohonan, lintasan joging, area bermain, dan monumen perjuangan. Ruang ini menunjukkan pentingnya kawasan hijau bagi kota yang padat. Berjalan perlahan dan mengamati keragaman pohon dapat menjadi kegiatan sederhana yang menyegarkan.

RENCANAKAN SESUAI CUACA

Kunjungi Curug Parigi pada pagi hari dan hanya ketika cuaca mendukung. Setelah makan siang, lanjutkan ke Hutan Kota Patriot. Bawa kembali sampah, gunakan fasilitas secara tertib, dan jangan mengganggu fungsi ruang publik bagi warga setempat.
CONTENT,
                false, '2026-09-03 09:00:00', ['kota-bekasi'], ['curug-parigi', 'hutan-kota-patriot'],
            ),
            $this->article(
                'Etika Sederhana saat Mengunjungi Ruang Budaya', 'etika-mengunjungi-ruang-budaya', 'tips-perjalanan',
                'Panduan bersikap saat berada di keraton, kampung adat, situs cagar budaya, dan ruang ibadah di Jawa Barat.',
                <<<'CONTENT'
Ruang budaya bukan hanya latar perjalanan. Keraton, kampung adat, situs cagar budaya, dan masjid memiliki fungsi, aturan, serta komunitas yang menjaganya. Sikap pengunjung menentukan apakah perjalanan memberi penghormatan atau justru menambah beban.

PAHAMI FUNGSI RUANG

Keraton dan situs seperti Gua Sunyaragi membawa nilai sejarah, sementara Kampung Adat Cireundeu adalah ruang hidup. Masjid Dian Al-Mahri terutama merupakan tempat ibadah. Perbedaan fungsi tersebut memengaruhi cara berpakaian, berbicara, memotret, dan menentukan waktu kunjungan.

MINTA IZIN SEBELUM MEMOTRET

Izin penting ketika kamera diarahkan kepada warga, kegiatan adat, koleksi, atau bagian ruang yang mungkin dibatasi. Tanda larangan bukan hambatan pengalaman, melainkan bagian dari upaya menjaga benda dan kenyamanan orang lain.

PILIH PENDAMPING LOKAL

Pemandu membantu menjelaskan konteks yang tidak selalu terlihat. Dengarkan tanpa memotong, hindari membuat kesimpulan cepat, dan dukung usaha lokal secara wajar. Setelah pulang, ceritakan pengalaman dengan bahasa yang menghormati masyarakat, bukan dengan label yang merendahkan atau melebih-lebihkan.
CONTENT,
                false, '2026-08-30 08:00:00', ['kota-cimahi', 'kota-cirebon', 'kota-depok'], ['kampung-adat-cireundeu', 'keraton-kasepuhan', 'gua-sunyaragi', 'masjid-kubah-emas'],
            ),
            $this->article(
                'Pangan Singkong dari Cireundeu: Lebih dari Pengganti Nasi', 'pangan-singkong-cireundeu', 'kuliner',
                'Melihat singkong sebagai bagian dari pengetahuan pangan, identitas, dan kemandirian masyarakat Kampung Adat Cireundeu.',
                <<<'CONTENT'
Di Kampung Adat Cireundeu, singkong memiliki kedudukan penting dalam kehidupan sehari-hari. Bahan ini diolah menjadi rasi—pangan berbentuk butiran yang dikonsumsi sebagai makanan pokok—serta beragam olahan lain yang memanfaatkan bagian tanaman secara cermat.

DARI KEBUN KE MEJA MAKAN

Mengolah singkong bukan proses instan. Ada pengetahuan mengenai pemilihan bahan, pengolahan, pengeringan, dan penyimpanan. Rangkaian tersebut menunjukkan bahwa tradisi pangan selalu berhubungan dengan lingkungan dan kemampuan masyarakat mengelola sumber daya di sekitarnya.

RASA YANG MEMBAWA CERITA

Ketika mencicipi olahan lokal, tanyakan cara penyajian dan konteksnya dengan sopan. Tidak semua hidangan selalu tersedia setiap saat karena kegiatan wisata berjalan berdampingan dengan kehidupan warga. Menghargai ketersediaan bahan adalah bagian dari menghargai pembuatnya.

MEMBAWA PULANG PEMAHAMAN

Wisata kuliner terbaik tidak berhenti pada rasa. Ia membantu kita melihat keputusan, kerja, dan nilai yang hadir di balik makanan. Jika membeli produk lokal, ikuti arahan pendamping dan pilih produk yang memang ditawarkan warga untuk pengunjung.
CONTENT,
                true, '2026-08-26 10:00:00', ['kota-cimahi'], ['kampung-adat-cireundeu'],
            ),
            $this->article(
                'Dua Hari Santai di Kota Sukabumi', 'dua-hari-santai-sukabumi', 'itinerary',
                'Rencana perjalanan dua hari yang menggabungkan kedatangan dengan kereta dan jeda hangat di kawasan Cikundul.',
                <<<'CONTENT'
Kota Sukabumi dapat dinikmati dengan ritme pelan. Stasiun Sukabumi memberi titik awal yang praktis, sedangkan Pemandian Air Panas Cikundul di Kecamatan Lembursitu menawarkan suasana berbeda di dekat aliran Sungai Cimandiri.

HARI PERTAMA: TIBA DAN MENGENAL PUSAT KOTA

Setelah tiba di Stasiun Sukabumi, perhatikan fasad bangunan dari area publik tanpa mengganggu pergerakan penumpang. Gunakan hari pertama untuk makan siang, berjalan santai di pusat kota, lalu beristirahat. Agenda yang ringan membuat tubuh siap untuk perjalanan hari berikutnya.

HARI KEDUA: MENUJU CIKUNDUL

Berangkatlah pada pagi hari menuju kawasan Pemandian Air Panas Cikundul. Nikmati fasilitas sesuai ketentuan pengelola dan jangan berendam terlalu lama. Permukaan di sekitar kolam dapat basah, sehingga alas kaki yang aman penting untuk dibawa.

PERIKSA KONDISI SEBELUM BERANGKAT

Kawasan Cikundul berdekatan dengan sungai. Hindari perjalanan saat hujan lebat dan selalu cek kondisi operasional terbaru. Setelah makan siang, kembali ke pusat kota dengan waktu cukup agar tidak terburu-buru mengejar jadwal kereta.
CONTENT,
                false, '2026-08-22 09:00:00', ['kota-sukabumi'], ['stasiun-sukabumi', 'pemandian-air-panas-cikundul'],
            ),
            $this->article(
                'Panduan Wisata Tenang di Kota Depok', 'panduan-wisata-tenang-depok', 'panduan-perjalanan',
                'Menyusun perjalanan yang menghormati waktu ibadah di Masjid Dian Al-Mahri dan ekosistem air Situ Pengasinan.',
                <<<'CONTENT'
Di tengah kepadatan kawasan metropolitan, Depok memiliki ruang yang mengajak pengunjung memperlambat langkah. Masjid Dian Al-Mahri menawarkan pengalaman arsitektur dan wisata religi, sementara Situ Pengasinan menghadirkan lanskap air di Kecamatan Sawangan.

HORMATI FUNGSI MASJID

Masjid Dian Al-Mahri dikenal melalui kubah berwarna emas dan kompleksnya yang luas. Namun fungsi utamanya tetap sebagai tempat ibadah. Kenakan pakaian sopan, jaga suara, patuhi pembagian ruang, dan hindari berkunjung sebagai rombongan wisata ketika ibadah utama berlangsung.

MENIKMATI TEPIAN SITU

Situ Pengasinan dapat menjadi tujuan sore setelah istirahat makan siang. Nikmati suasana dari area yang aman dan jangan membuang sampah ke air. Kegiatan serta kondisi fasilitas di sekitar situ dapat berubah, jadi periksa informasi terbaru sebelum berangkat.

ANTISIPASI LALU LINTAS

Jarak di peta tidak selalu menggambarkan waktu tempuh di Depok. Berikan jeda antarlokasi dan hindari jam sibuk jika memungkinkan. Perjalanan yang hanya memuat dua titik memberi ruang untuk berkunjung dengan tenang sekaligus tetap menghormati ritme kota.
CONTENT,
                false, '2026-08-18 08:00:00', ['kota-depok'], ['masjid-kubah-emas', 'situ-pengasinan'],
            ),
            $this->article(
                'Tips Mengajak Keluarga Belajar di Museum', 'tips-museum-bersama-keluarga', 'tips-perjalanan',
                'Cara sederhana membuat kunjungan ke Museum Geologi dan Museum Zoologi tetap menarik bagi anak maupun orang dewasa.',
                <<<'CONTENT'
Museum dapat menjadi ruang belajar yang hidup ketika keluarga tidak mencoba melihat semua koleksi sekaligus. Museum Geologi Bandung dan Museum Zoologi Bogor memiliki tema berbeda, tetapi keduanya dapat dinikmati dengan strategi kunjungan yang sama.

PILIH SATU PERTANYAAN UTAMA

Sebelum masuk, ajak anak memilih pertanyaan sederhana: bagaimana fosil terbentuk, mengapa bentuk hewan berbeda, atau apa hubungan lingkungan dengan kehidupan. Pertanyaan memberi arah sehingga kunjungan tidak terasa seperti berjalan tanpa tujuan.

BERI WAKTU UNTUK MENGAMATI

Jangan terburu-buru membaca setiap label. Pilih beberapa koleksi, amati bentuknya, lalu baca informasi yang paling relevan. Hindari menyentuh koleksi dan patuhi batas fotografi. Beristirahatlah sebelum anak mulai lelah.

LANJUTKAN CERITA SETELAH PULANG

Minta setiap anggota keluarga menyebutkan satu hal yang paling diingat. Catatan kecil atau gambar dapat memperpanjang pengalaman belajar. Selalu periksa jadwal, akses, dan ketentuan tiket melalui kanal resmi karena informasi operasional dapat berubah.
CONTENT,
                false, '2026-08-14 09:30:00', ['kota-bandung', 'kota-bogor'], ['museum-geologi-bandung', 'museum-zoologi-bogor'],
            ),
            $this->article(
                'Mengenal Rasa Pesisir dalam Kuliner Cirebon', 'rasa-pesisir-cirebon', 'kuliner',
                'Kuliner Cirebon memperlihatkan karakter kota persinggahan melalui sajian yang kuat, praktis, dan lekat dengan keseharian.',
                <<<'CONTENT'
Cirebon berkembang di jalur pesisir yang mempertemukan orang, barang, dan kebiasaan makan. Karakter itu terasa dalam ragam kulinernya: empal gentong yang berkuah, nasi jamblang dengan pilihan lauk, serta tahu gejrot dengan rasa manis, asam, dan pedas.

MAKANAN DAN RITME KOTA

Nasi jamblang memberi kebebasan memilih lauk sesuai selera. Empal gentong menawarkan kuah yang kaya rempah, sedangkan tahu gejrot sering dinikmati sebagai kudapan. Ketiganya menunjukkan bahwa pengalaman kuliner Cirebon tidak harus dibangun dari satu jamuan besar.

PADUKAN DENGAN PERJALANAN BUDAYA

Kunjungan ke Keraton Kasepuhan atau Gua Sunyaragi dapat diselingi waktu makan yang cukup. Hindari menyusun jadwal terlalu rapat; menikmati makanan lokal membutuhkan waktu untuk memilih, bertanya, dan memahami cara penyajiannya.

MEMILIH DENGAN BIJAK

Pilih tempat makan yang menjaga kebersihan dan menampilkan harga dengan jelas. Tanyakan tingkat kepedasan atau bahan tertentu bila memiliki kebutuhan khusus. Rekomendasi dapat berubah, jadi gunakan informasi terbaru tanpa kehilangan kesempatan untuk mencoba usaha lokal yang dikelola warga.
CONTENT,
                false, '2026-08-10 11:00:00', ['kota-cirebon'], [],
            ),
        ];
    }

    /**
     * @param  array<int, string>  $destinations
     * @param  array<int, string>  $attractions
     * @return array<string, mixed>
     */
    private function article(string $title, string $slug, string $category, string $summary, string $content, bool $featured, string $publishedAt, array $destinations, array $attractions): array
    {
        return [
            'judul' => $title,
            'slug' => $slug,
            'kategori' => $category,
            'ringkasan' => $summary,
            'konten' => $content,
            'status' => true,
            'unggulan' => $featured,
            'tanggal_publikasi' => $publishedAt,
            'destinasi' => $destinations,
            'tempat_wisata' => $attractions,
        ];
    }
}
