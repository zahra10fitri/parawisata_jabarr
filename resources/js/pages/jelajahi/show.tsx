import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Clock3, MapPin, Mountain, Sun, Ticket } from 'lucide-react';
import { index } from '@/routes/jelajahi';
import { ticketPrice } from '@/types/jelajahi';
import type { ExploreDetail } from '@/types/jelajahi';

export default function DetailWisata({ wisata }: { wisata: ExploreDetail }) {
    return (
        <>
            <Head title={wisata.nama}>
                {wisata.deskripsi_singkat && (
                    <meta
                        name="description"
                        content={wisata.deskripsi_singkat}
                    />
                )}
            </Head>
            <div className="mx-auto max-w-7xl px-5 pt-8 sm:px-8">
                <Link
                    href={index()}
                    className="mb-7 inline-flex items-center gap-2 text-sm font-medium text-[#587064]"
                >
                    <ArrowLeft size={16} aria-hidden="true" />
                    Kembali menjelajahi
                </Link>
                <div className="mb-8 max-w-4xl">
                    <Link
                        href={index({
                            query: { destinasi_id: wisata.destinasi.id },
                        })}
                        className="inline-flex items-center gap-1.5 text-xs font-semibold tracking-[0.15em] text-[#587064] uppercase"
                    >
                        <MapPin size={15} aria-hidden="true" />
                        {wisata.destinasi.nama}
                    </Link>
                    <h1 className="mt-4 font-serif text-4xl leading-tight break-words sm:text-6xl">
                        {wisata.nama}
                    </h1>
                    {wisata.deskripsi_singkat && (
                        <p className="mt-5 max-w-2xl text-base leading-relaxed whitespace-pre-wrap text-[#587064]">
                            {wisata.deskripsi_singkat}
                        </p>
                    )}
                </div>
                <div className="relative aspect-[4/3] overflow-hidden rounded-2xl bg-[#e3eadb] sm:aspect-[21/9] sm:rounded-[2rem]">
                    {wisata.foto_url ? (
                        <img
                            src={wisata.foto_url}
                            alt={wisata.nama}
                            fetchPriority="high"
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex h-full flex-col items-center justify-center gap-3 text-[#658064]">
                            <Mountain
                                size={70}
                                strokeWidth={1}
                                aria-hidden="true"
                            />
                            <p className="text-sm">Foto utama belum tersedia</p>
                        </div>
                    )}
                    {wisata.unggulan && (
                        <span className="absolute top-5 left-5 rounded-full bg-[#f7f5ee] px-4 py-2 text-xs font-semibold">
                            Pilihan unggulan
                        </span>
                    )}
                </div>
                <div className="mt-10 grid items-start gap-10 lg:grid-cols-[1fr_340px] lg:gap-16">
                    <div className="min-w-0 space-y-10">
                        <section>
                            <h2 className="mb-4 font-serif text-3xl">
                                Tentang tempat ini
                            </h2>
                            <p className="leading-8 break-words whitespace-pre-wrap text-[#587064]">
                                {wisata.deskripsi_lengkap ||
                                    'Deskripsi lengkap belum tersedia.'}
                            </p>
                        </section>
                        <section>
                            <h2 className="mb-4 font-serif text-3xl">
                                Pengalaman untukmu
                            </h2>
                            <div className="flex flex-wrap gap-2">
                                {wisata.pengalaman.length ? (
                                    wisata.pengalaman.map((item) => (
                                        <Link
                                            key={item.id}
                                            href={index({
                                                query: {
                                                    pengalaman_id: item.id,
                                                },
                                            })}
                                            className="rounded-full border border-[#245b43]/20 bg-[#e7eedf] px-4 py-2 text-sm hover:bg-[#d7e8cc]"
                                        >
                                            {item.nama}
                                        </Link>
                                    ))
                                ) : (
                                    <p className="text-sm text-[#587064]">
                                        Informasi pengalaman belum tersedia.
                                    </p>
                                )}
                            </div>
                        </section>
                        <div className="grid gap-8 sm:grid-cols-2">
                            {(
                                [
                                    { key: 'aktivitas', label: 'Aktivitas' },
                                    { key: 'fasilitas', label: 'Fasilitas' },
                                ] as const
                            ).map((group) => (
                                <section key={group.key}>
                                    <h2 className="mb-4 font-serif text-2xl">
                                        {group.label}
                                    </h2>
                                    {wisata[group.key].length ? (
                                        <ul className="space-y-3 text-sm text-[#587064]">
                                            {wisata[group.key].map((item) => (
                                                <li
                                                    key={item.id}
                                                    className="flex items-start gap-2"
                                                >
                                                    <span
                                                        aria-hidden="true"
                                                        className="mt-1.5 size-1.5 shrink-0 rounded-full bg-[#658064]"
                                                    />
                                                    {item.nama}
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-sm text-[#587064]">
                                            Informasi belum tersedia.
                                        </p>
                                    )}
                                </section>
                            ))}
                        </div>
                        {wisata.tips && (
                            <section className="rounded-2xl bg-[#e7eedf] p-6 sm:p-8">
                                <p className="mb-3 text-xs font-semibold tracking-[0.12em] text-[#587064] uppercase">
                                    Catatan sebelum berangkat
                                </p>
                                <h2 className="mb-3 font-serif text-2xl">
                                    Tips untuk perjalananmu
                                </h2>
                                <p className="text-sm leading-7 break-words whitespace-pre-wrap text-[#47614e]">
                                    {wisata.tips}
                                </p>
                            </section>
                        )}
                    </div>
                    <aside
                        aria-label="Informasi kunjungan"
                        className="rounded-3xl border border-[#193e32]/10 bg-[#fffef9] p-5 sm:p-8 lg:sticky lg:top-6"
                    >
                        <h2 className="mb-6 font-serif text-2xl">
                            Rencanakan kunjunganmu
                        </h2>
                        <dl className="space-y-6">
                            <div>
                                <dt className="flex items-center gap-2 text-xs font-semibold tracking-wide text-[#587064] uppercase">
                                    <MapPin size={16} aria-hidden="true" />
                                    Lokasi
                                </dt>
                                <dd className="mt-2 text-sm leading-relaxed break-words whitespace-pre-wrap">
                                    {wisata.lokasi ||
                                        'Informasi lokasi belum tersedia.'}
                                </dd>
                            </div>
                            <div>
                                <dt className="flex items-center gap-2 text-xs font-semibold tracking-wide text-[#587064] uppercase">
                                    <Clock3 size={16} aria-hidden="true" />
                                    Jam operasional
                                </dt>
                                <dd className="mt-2 text-sm leading-relaxed break-words whitespace-pre-wrap">
                                    {wisata.jam_operasional ||
                                        'Informasi jam operasional belum tersedia.'}
                                </dd>
                            </div>
                            <div>
                                <dt className="flex items-center gap-2 text-xs font-semibold tracking-wide text-[#587064] uppercase">
                                    <Ticket size={16} aria-hidden="true" />
                                    Harga tiket
                                </dt>
                                <dd className="mt-2 text-lg font-semibold">
                                    {ticketPrice(wisata.harga_tiket)}
                                </dd>
                            </div>
                            <div>
                                <dt className="flex items-center gap-2 text-xs font-semibold tracking-wide text-[#587064] uppercase">
                                    <Sun size={16} aria-hidden="true" />
                                    Waktu terbaik
                                </dt>
                                <dd className="mt-2 text-sm leading-relaxed break-words whitespace-pre-wrap">
                                    {wisata.waktu_terbaik ||
                                        'Informasi waktu terbaik belum tersedia.'}
                                </dd>
                            </div>
                        </dl>
                    </aside>
                </div>
                <section className="mt-14 border-t border-[#193e32]/15 pt-10">
                    <div className="mb-6 flex flex-wrap items-baseline justify-between gap-3">
                        <h2 className="font-serif text-3xl">
                            Sekilas dari dekat
                        </h2>
                        <span className="text-sm text-[#587064]">
                            {wisata.galeri.length} foto
                        </span>
                    </div>
                    {wisata.galeri.length ? (
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                            {wisata.galeri.map((photo, i) => (
                                <img
                                    key={photo.id}
                                    src={photo.url}
                                    alt={`${wisata.nama}, foto galeri ${i + 1}`}
                                    loading="lazy"
                                    className="aspect-[4/3] w-full rounded-2xl object-cover"
                                />
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-[#587064]">
                            Foto galeri belum tersedia.
                        </p>
                    )}
                </section>
            </div>
        </>
    );
}
