import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Compass, Search } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import WisataCard from '@/components/wisata-card';
import PublicHero from '@/components/public-hero';
import { index } from '@/routes/jelajahi';
import type { ExploreOption, ExplorePagination } from '@/types/jelajahi';

type Filters = { search: string; destinasi_id: string; pengalaman_id: string };
export default function Jelajahi({
    wisata,
    filters,
    destinasiOptions,
    pengalamanOptions,
}: {
    wisata: ExplorePagination;
    filters: Filters;
    destinasiOptions: ExploreOption[];
    pengalamanOptions: ExploreOption[];
}) {
    const [values, setValues] = useState(filters);
    const [processing, setProcessing] = useState(false);
    const { errors } = usePage().props;
    const heroPhoto = wisata.data.find((item) => item.foto_url)?.foto_url;
    const hasFilters = Boolean(
        filters.search || filters.destinasi_id || filters.pengalaman_id,
    );

    function search(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(index.url(), values, {
            preserveState: false,
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => setProcessing(false),
        });
    }

    return (
        <>
            <Head title="Jelajahi Jawa Barat">
                <meta
                    name="description"
                    content="Temukan tempat wisata Jawa Barat. Jelajahi destinasi, pilih pengalaman, dan temukan informasi untuk perjalananmu."
                />
            </Head>
            <PublicHero
                eyebrow="Jelajahi Jawa Barat"
                title="Lebih dekat dengan alam & cerita baru"
                description="Dari perjalanan kecil hingga pengalaman yang berkesan. Temukan sudut Jawa Barat yang ingin kamu jelajahi."
                imageUrl={heroPhoto}
            />
            <div className="mx-auto max-w-7xl px-5 sm:px-8">
                <section
                    aria-label="Cari tempat wisata"
                    className="relative mx-auto -mt-5 max-w-6xl rounded-2xl border border-[#193e32]/10 bg-[#fffef9] p-5 shadow-lg shadow-[#193e32]/5 sm:p-6"
                >
                    <form
                        onSubmit={search}
                        className="grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_auto]"
                    >
                        <div>
                            <label
                                htmlFor="search"
                                className="mb-2 block text-xs font-semibold tracking-wide"
                            >
                                MAU KE MANA?
                            </label>
                            <div className="relative">
                                <Search
                                    size={17}
                                    className="absolute top-3 left-3 text-[#587064]"
                                    aria-hidden="true"
                                />
                                <input
                                    id="search"
                                    name="search"
                                    type="search"
                                    maxLength={255}
                                    placeholder="Cari nama tempat wisata"
                                    value={values.search}
                                    onChange={(event) =>
                                        setValues({
                                            ...values,
                                            search: event.target.value,
                                        })
                                    }
                                    className="h-11 w-full rounded-xl border border-[#193e32]/20 bg-transparent pr-3 pl-10 text-sm outline-offset-2"
                                />
                            </div>
                        </div>
                        <div>
                            <label
                                htmlFor="destinasi"
                                className="mb-2 block text-xs font-semibold tracking-wide"
                            >
                                DESTINASI
                            </label>
                            <select
                                id="destinasi"
                                value={values.destinasi_id}
                                onChange={(event) =>
                                    setValues({
                                        ...values,
                                        destinasi_id: event.target.value,
                                    })
                                }
                                className="h-11 w-full rounded-xl border border-[#193e32]/20 bg-[#fffef9] px-3 text-sm"
                            >
                                <option value="">Semua destinasi</option>
                                {destinasiOptions.map((item) => (
                                    <option key={item.id} value={item.id}>
                                        {item.nama}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label
                                htmlFor="pengalaman"
                                className="mb-2 block text-xs font-semibold tracking-wide"
                            >
                                PENGALAMAN
                            </label>
                            <select
                                id="pengalaman"
                                value={values.pengalaman_id}
                                onChange={(event) =>
                                    setValues({
                                        ...values,
                                        pengalaman_id: event.target.value,
                                    })
                                }
                                className="h-11 w-full rounded-xl border border-[#193e32]/20 bg-[#fffef9] px-3 text-sm"
                            >
                                <option value="">Semua pengalaman</option>
                                {pengalamanOptions.map((item) => (
                                    <option key={item.id} value={item.id}>
                                        {item.nama}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#245b43] px-6 text-sm font-semibold text-white hover:bg-[#193e32] disabled:opacity-60"
                        >
                            {processing ? 'Mencari...' : 'Jelajahi'}
                            <ArrowRight size={17} aria-hidden="true" />
                        </button>
                        {Object.entries(errors).map(([key, message]) => (
                            <p
                                key={key}
                                role="alert"
                                className="text-sm text-red-700"
                            >
                                {message}
                            </p>
                        ))}
                    </form>
                </section>
                <section
                    className="pt-12 sm:pt-16"
                    aria-label="Hasil jelajah"
                    aria-busy={processing}
                >
                    <div className="mb-7 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="mb-2 text-xs font-semibold tracking-[0.15em] text-[#658064] uppercase">
                                Temukan favoritmu
                            </p>
                            <h2 className="font-serif text-3xl sm:text-4xl">
                                {hasFilters
                                    ? 'Hasil penjelajahanmu'
                                    : 'Tempat untuk cerita berikutnya'}
                            </h2>
                            <p
                                aria-live="polite"
                                className="mt-3 text-sm text-[#587064]"
                            >
                                {wisata.total} tempat wisata
                                {hasFilters
                                    ? ' sesuai pilihanmu'
                                    : ' menanti untuk dijelajahi'}
                                .
                            </p>
                        </div>
                        {hasFilters && (
                            <Link
                                href={index()}
                                className="text-sm font-medium underline underline-offset-4"
                            >
                                Reset pencarian & filter
                            </Link>
                        )}
                    </div>
                    {wisata.data.length ? (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {wisata.data.map((item) => (
                                <WisataCard key={item.id} wisata={item} />
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-3xl border border-dashed border-[#193e32]/25 bg-[#edf0e5] px-6 py-16 text-center">
                            <Compass
                                className="mx-auto mb-4 text-[#587064]"
                                size={40}
                                strokeWidth={1}
                                aria-hidden="true"
                            />
                            <h3 className="font-serif text-2xl">
                                {hasFilters
                                    ? 'Belum menemukan yang cocok?'
                                    : 'Perjalanan baru sedang disiapkan.'}
                            </h3>
                            <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-[#587064]">
                                {hasFilters
                                    ? 'Coba kata kunci lain atau perluas pilihan destinasi dan pengalamanmu.'
                                    : 'Tempat wisata akan tampil di sini setelah tersedia. Kembali lagi untuk menemukan cerita baru.'}
                            </p>
                            {hasFilters && (
                                <Link
                                    href={index()}
                                    className="mt-6 inline-block rounded-full bg-[#245b43] px-5 py-3 text-sm font-medium text-white"
                                >
                                    Lihat semua tempat
                                </Link>
                            )}
                        </div>
                    )}
                    {wisata.total > 0 && (
                        <nav
                            aria-label="Pagination jelajahi"
                            className="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-[#193e32]/15 pt-6"
                        >
                            {wisata.prev_page_url ? (
                                <Link
                                    href={wisata.prev_page_url}
                                    preserveScroll
                                    className="inline-flex items-center gap-2 rounded-full border border-[#193e32]/20 px-4 py-2 text-sm"
                                >
                                    <ArrowLeft size={16} aria-hidden="true" />
                                    Sebelumnya
                                </Link>
                            ) : (
                                <span className="px-4 py-2 text-sm text-[#587064]/60">
                                    Sebelumnya
                                </span>
                            )}
                            <span className="text-sm text-[#587064]">
                                Halaman {wisata.current_page} dari{' '}
                                {wisata.last_page}
                            </span>
                            {wisata.next_page_url ? (
                                <Link
                                    href={wisata.next_page_url}
                                    preserveScroll
                                    className="inline-flex items-center gap-2 rounded-full border border-[#193e32]/20 px-4 py-2 text-sm"
                                >
                                    Berikutnya
                                    <ArrowRight size={16} aria-hidden="true" />
                                </Link>
                            ) : (
                                <span className="px-4 py-2 text-sm text-[#587064]/60">
                                    Berikutnya
                                </span>
                            )}
                        </nav>
                    )}
                </section>
            </div>
        </>
    );
}
