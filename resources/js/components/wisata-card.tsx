import { Link } from '@inertiajs/react';
import { ArrowUpRight, MapPin, Mountain } from 'lucide-react';
import { show } from '@/routes/jelajahi';
import { ticketPrice } from '@/types/jelajahi';
import type { ExploreWisata } from '@/types/jelajahi';

export default function WisataCard({ wisata }: { wisata: ExploreWisata }) {
    return (
        <article className="group overflow-hidden rounded-3xl border border-[#193e32]/10 bg-[#fffef9] transition-shadow hover:shadow-lg">
            <Link
                href={show(wisata.slug)}
                className="block h-full"
                aria-label={`Jelajahi ${wisata.nama}`}
            >
                <div className="relative aspect-[4/3] overflow-hidden bg-[#e3eadb]">
                    {wisata.foto_url ? (
                        <img
                            src={wisata.foto_url}
                            alt={wisata.nama}
                            loading="lazy"
                            className="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-105"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center text-[#78917d]">
                            <Mountain
                                size={64}
                                strokeWidth={1}
                                aria-hidden="true"
                            />
                            <span className="sr-only">Foto belum tersedia</span>
                        </div>
                    )}
                    {wisata.unggulan && (
                        <span className="absolute top-4 left-4 rounded-full bg-[#f7f5ee] px-3 py-1.5 text-xs font-semibold text-[#245b43]">
                            Pilihan unggulan
                        </span>
                    )}
                    <span className="absolute right-4 bottom-4 flex size-10 items-center justify-center rounded-full bg-white text-[#245b43]">
                        <ArrowUpRight size={20} aria-hidden="true" />
                    </span>
                </div>
                <div className="space-y-3 p-5 sm:p-6">
                    <p className="flex items-center gap-1.5 text-xs font-medium tracking-wide text-[#587064]">
                        <MapPin size={14} aria-hidden="true" />
                        {wisata.destinasi.nama}
                    </p>
                    <h2 className="font-serif text-2xl leading-tight break-words">
                        {wisata.nama}
                    </h2>
                    {wisata.deskripsi_singkat && (
                        <p className="line-clamp-2 text-sm leading-relaxed text-[#587064]">
                            {wisata.deskripsi_singkat}
                        </p>
                    )}
                    <div className="flex flex-wrap gap-2">
                        {wisata.pengalaman.slice(0, 2).map((item) => (
                            <span
                                key={item.id}
                                className="rounded-full bg-[#edf0e5] px-2.5 py-1 text-xs text-[#47614e]"
                            >
                                {item.nama}
                            </span>
                        ))}
                    </div>
                    <p className="border-t border-[#193e32]/10 pt-3 text-sm font-medium">
                        {ticketPrice(wisata.harga_tiket)}
                    </p>
                </div>
            </Link>
        </article>
    );
}
