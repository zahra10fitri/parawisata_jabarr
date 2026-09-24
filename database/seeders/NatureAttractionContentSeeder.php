<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class NatureAttractionContentSeeder extends Seeder
{
    public function run(): void
    {
        $places = $this->places();
        DB::transaction(function () use ($places): void {
            foreach ($this->extraDestinations() as $destination) {
                Destinasi::query()->updateOrCreate(['slug' => $destination['slug']], $destination);
            }
            $destinations = Destinasi::query()->pluck('id', 'slug');
            $experiences = Pengalaman::query()->pluck('id', 'slug');
            $activities = Aktivitas::query()->pluck('id', 'slug');
            $facilities = Fasilitas::query()->pluck('id', 'slug');
            $obsoletePlaceIds = TempatWisata::query()
                ->whereNotIn('slug', array_column($places, 'slug'))
                ->pluck('id');

            DB::table('paket_perjalanan_tempat_wisata')
                ->whereIn('tempat_wisata_id', $obsoletePlaceIds)
                ->delete();

            DB::table('artikel_tempat_wisata')
                ->whereIn('tempat_wisata_id', $obsoletePlaceIds)
                ->delete();

            TempatWisata::query()->whereKey($obsoletePlaceIds)->delete();
            foreach ($places as $place) {
                $asset = database_path("seeders/assets/tempat-wisata/{$place['foto']}");
                if (! File::isFile($asset)) {
                    throw new RuntimeException("Aset foto tidak ditemukan: {$asset}");
                }
                $path = "tempat-wisata/{$place['foto']}";
                Storage::disk('public')->put($path, File::get($asset));
                $record = TempatWisata::query()->updateOrCreate(['slug' => $place['slug']], [
                    'nama' => $place['nama'], 'destinasi_id' => $destinations[$place['destinasi']],
                    'deskripsi_singkat' => $place['singkat'], 'deskripsi_lengkap' => $place['lengkap'], 'lokasi' => $place['lokasi'],
                    'jam_operasional' => null, 'harga_tiket' => null, 'waktu_terbaik' => null, 'tips' => null,
                    'status' => true, 'unggulan' => true, 'urutan' => $place['urutan'], 'foto_utama' => $path,
                ]);
                $record->pengalaman()->sync($this->ids($experiences, $place['pengalaman']));
                $record->aktivitas()->sync($this->ids($activities, $place['aktivitas']));
                $record->fasilitas()->sync($this->ids($facilities, $place['fasilitas']));
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    private function extraDestinations(): array
    {
        return [
            $this->destination('Kabupaten Bandung Barat', 'kabupaten-bandung-barat', 'Kabupaten Bandung Barat, Jawa Barat', 90),
            $this->destination('Kota Tasikmalaya', 'kota-tasikmalaya', 'Kota Tasikmalaya, Jawa Barat', 100),
            $this->destination('Kabupaten Garut', 'kabupaten-garut', 'Kabupaten Garut, Jawa Barat', 110),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function places(): array
    {
        return [
            $this->place('Babakan Siliwangi City Forest Path', 'babakan-siliwangi-city-forest-path', 'kota-bandung', 'Jalur pejalan kaki di hutan kota Babakan Siliwangi.', 'Forest Walk Babakan Siliwangi merupakan jalur pejalan kaki di kawasan hutan kota Babakan Siliwangi.', 'Babakan Siliwangi, Kota Bandung, Jawa Barat', 'babakan-siliwangi-city-forest-path.jpg', 10, ['alam', 'keluarga'], ['fotografi'], ['toilet']),
            $this->place('Kebun Raya Bogor', 'kebun-raya-bogor', 'kota-bogor', 'Kebun botani bersejarah dengan koleksi tumbuhan tropis.', 'Kebun Raya Bogor merupakan kebun botani di Kota Bogor untuk konservasi, pendidikan, dan rekreasi alam.', 'Kota Bogor, Jawa Barat', 'kebun-raya-bogor.webp', 20, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala']),
            $this->place('Studio Alam TVRI', 'studio-alam-tvri', 'kota-depok', 'Kawasan studio luar ruang TVRI di Depok.', 'Studio Alam TVRI adalah kawasan studio luar ruang di Sukmajaya, Kota Depok.', 'Sukmajaya, Kota Depok, Jawa Barat', 'studio-alam-tvri.jpg', 30, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet']),
            $this->place('Situ Rawa Gede', 'situ-rawa-gede', 'kota-bekasi', 'Destinasi wisata alam danau di Bojong Menteng.', 'Situ Rawa Gede berada di Kelurahan Bojong Menteng, Kecamatan Rawalumbu, Kota Bekasi.', 'Bojong Menteng, Rawalumbu, Kota Bekasi, Jawa Barat', 'situ-rawa-gede.jpg', 40, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala', 'tempat-makan']),
            $this->place('Situ Gunung Sukabumi', 'situ-gunung-sukabumi', 'kota-sukabumi', 'Danau di kawasan wisata Situ Gunung.', 'Situ Gunung berada di kawasan wisata Situ Gunung, Desa Gedepangrango, Kecamatan Kadudampit, Kabupaten Sukabumi.', 'Kadudampit, Kabupaten Sukabumi, Jawa Barat', 'situ-gunung-sukabumi.jpg', 50, ['alam', 'petualangan'], ['hiking', 'fotografi'], ['area-parkir', 'toilet']),
            $this->place('Curug Cimahi / Rainbow Waterfalls', 'curug-cimahi-rainbow-waterfalls', 'kabupaten-bandung-barat', 'Air terjun Curug Cimahi yang juga dikenal sebagai Rainbow Waterfalls.', 'Curug Cimahi atau Air Terjun Pelangi berada di Kabupaten Bandung Barat.', 'Kabupaten Bandung Barat, Jawa Barat', 'curug-cimahi-rainbow-waterfalls.jpg', 60, ['alam', 'petualangan'], ['hiking', 'fotografi'], ['area-parkir', 'toilet']),
            $this->place('Kawasan Mangrove Kasendanan', 'kawasan-mangrove-kasendanan', 'kota-cirebon', 'Kawasan mangrove pesisir di Kota Cirebon.', 'Kawasan mangrove Kasendanan berada di pesisir Kota Cirebon dan mendukung ekosistem pesisir.', 'Kesenden, Kota Cirebon, Jawa Barat', 'kawasan-mangrove-kasendanan.jpg', 70, ['alam'], ['fotografi'], ['area-parkir']),
            $this->place('Situ Gede', 'situ-gede-tasikmalaya', 'kota-tasikmalaya', 'Danau wisata di Kecamatan Mangkubumi.', 'Situ Gede merupakan kawasan danau wisata di Kelurahan Linggajaya, Kecamatan Mangkubumi, Kota Tasikmalaya.', 'Linggajaya, Mangkubumi, Kota Tasikmalaya, Jawa Barat', 'situ-gede-tasikmalaya.jpg', 80, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet', 'musala', 'tempat-makan']),
            $this->place('Situ Mustika', 'situ-mustika', 'kota-banjar', 'Danau di kawasan hutan Kota Banjar.', 'Situ Mustika merupakan danau wisata di kawasan hutan Kota Banjar.', 'Purwaharja, Kota Banjar, Jawa Barat', 'situ-mustika.webp', 90, ['alam', 'keluarga'], ['fotografi'], ['area-parkir', 'toilet']),
            $this->place('Taman Wisata Alam Gunung Papandayan', 'taman-wisata-alam-gunung-papandayan', 'kabupaten-garut', 'Kawasan wisata alam Gunung Papandayan di Garut.', 'Taman Wisata Alam Gunung Papandayan berada di Cisurupan, Kabupaten Garut.', 'Cisurupan, Kabupaten Garut, Jawa Barat', 'taman-wisata-alam-gunung-papandayan.jpg', 100, ['alam', 'petualangan'], ['hiking', 'camping', 'fotografi'], ['area-parkir', 'toilet']),
        ];
    }

    private function destination(string $name, string $slug, string $location, int $order): array
    {
        return ['nama' => $name, 'slug' => $slug, 'deskripsi_singkat' => "Destinasi {$name} di Jawa Barat.", 'deskripsi_lengkap' => "{$name} berada di Jawa Barat.", 'lokasi' => $location, 'waktu_terbaik' => null, 'cara_menuju' => null, 'transportasi_lokal' => null, 'tips' => null, 'status' => true, 'unggulan' => false, 'urutan' => $order, 'foto_utama' => null];
    }

    private function place(string $name, string $slug, string $destination, string $summary, string $description, string $location, string $photo, int $order, array $experiences, array $activities, array $facilities): array
    {
        return compact('name', 'slug', 'destination', 'summary', 'description', 'location', 'photo', 'order', 'experiences', 'activities', 'facilities') + ['nama' => $name, 'destinasi' => $destination, 'singkat' => $summary, 'lengkap' => $description, 'lokasi' => $location, 'foto' => $photo, 'urutan' => $order, 'pengalaman' => $experiences, 'aktivitas' => $activities, 'fasilitas' => $facilities];
    }

    private function ids($available, array $slugs): array
    {
        return collect($slugs)->map(fn (string $slug): int => $available[$slug] ?? throw new RuntimeException("Master data tidak ditemukan: {$slug}"))->all();
    }
}
