export type DestinasiSummary = {
    id: number;
    nama: string;
    slug: string;
    foto_utama: string | null;
    foto_utama_url: string | null;
    lokasi: string | null;
    status: boolean;
    unggulan: boolean;
    urutan: number;
};

export type Destinasi = DestinasiSummary & {
    deskripsi_singkat: string | null;
    deskripsi_lengkap: string | null;
    waktu_terbaik: string | null;
    cara_menuju: string | null;
    transportasi_lokal: string | null;
    tips: string | null;
};

export type PaginatedDestinasi = {
    data: DestinasiSummary[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export const destinasiTextFields = [
    { name: 'deskripsi_singkat', label: 'Deskripsi singkat', max: 1000 },
    { name: 'deskripsi_lengkap', label: 'Deskripsi lengkap', max: 50000 },
    { name: 'waktu_terbaik', label: 'Waktu terbaik', max: 5000 },
    { name: 'cara_menuju', label: 'Cara menuju', max: 5000 },
    { name: 'transportasi_lokal', label: 'Transportasi lokal', max: 5000 },
    { name: 'tips', label: 'Tips', max: 5000 },
] as const;
