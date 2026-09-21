export type ExploreOption = { id: number; nama: string };
export type ExploreWisata = {
    id: number;
    nama: string;
    slug: string;
    deskripsi_singkat: string | null;
    lokasi: string | null;
    harga_tiket: string | null;
    unggulan: boolean;
    foto_url: string | null;
    destinasi: ExploreOption;
    pengalaman: ExploreOption[];
};
export type ExploreDetail = ExploreWisata & {
    deskripsi_lengkap: string | null;
    jam_operasional: string | null;
    waktu_terbaik: string | null;
    tips: string | null;
    aktivitas: ExploreOption[];
    fasilitas: ExploreOption[];
    galeri: { id: number; url: string }[];
};
export type ExplorePagination = {
    data: ExploreWisata[];
    total: number;
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};
export function ticketPrice(price: string | null): string {
    if (price === null) {
        return 'Harga tiket belum tersedia';
    }
    if (Number(price) === 0) {
        return 'Gratis';
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 2,
    }).format(Number(price));
}
