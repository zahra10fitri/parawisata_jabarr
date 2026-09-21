export type MasterOption = { id: number; nama: string; status?: boolean };
export type GalleryPhoto = {
    id: number;
    foto: string;
    url: string;
    urutan: number;
};
export type TempatWisataSummary = {
    id: number;
    destinasi_id: number;
    destinasi: MasterOption;
    nama: string;
    slug: string;
    foto_utama: string | null;
    foto_utama_url: string | null;
    lokasi: string | null;
    status: boolean;
    unggulan: boolean;
    urutan: number;
};
export type TempatWisata = TempatWisataSummary & {
    deskripsi_singkat: string | null;
    deskripsi_lengkap: string | null;
    jam_operasional: string | null;
    harga_tiket: string | null;
    waktu_terbaik: string | null;
    tips: string | null;
    pengalaman: MasterOption[];
    aktivitas: MasterOption[];
    fasilitas: MasterOption[];
    galeri: GalleryPhoto[];
};
export type WisataOptions = {
    destinasi: MasterOption[];
    pengalaman: MasterOption[];
    aktivitas: MasterOption[];
    fasilitas: MasterOption[];
};
export type PaginatedTempatWisata = {
    data: TempatWisataSummary[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};
export const wisataTextFields = [
    { name: 'deskripsi_singkat', label: 'Deskripsi singkat', max: 1000 },
    { name: 'deskripsi_lengkap', label: 'Deskripsi lengkap', max: 50000 },
    { name: 'jam_operasional', label: 'Jam operasional', max: 5000 },
    { name: 'waktu_terbaik', label: 'Waktu terbaik', max: 5000 },
    { name: 'tips', label: 'Tips', max: 5000 },
] as const;
export const wisataRelations = [
    { name: 'pengalaman', label: 'Pengalaman' },
    { name: 'aktivitas', label: 'Aktivitas' },
    { name: 'fasilitas', label: 'Fasilitas' },
] as const;
