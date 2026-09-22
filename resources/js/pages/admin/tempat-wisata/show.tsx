import { Head, Link } from '@inertiajs/react';
import DeleteTempatWisataDialog from '@/components/delete-tempat-wisata-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { show as showDestinasi } from '@/routes/admin/destinasi';
import { edit, index } from '@/routes/admin/tempat-wisata';
import { wisataRelations, wisataTextFields } from '@/types/tempat-wisata';
import type { TempatWisata } from '@/types/tempat-wisata';

export default function ShowTempatWisata({
    tempatWisata,
}: {
    tempatWisata: TempatWisata;
}) {
    return (
        <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <Head title={tempatWisata.nama} />
            <div className="flex flex-wrap items-center justify-between gap-4">
                <h1 className="min-w-0 text-2xl font-semibold break-words">
                    {tempatWisata.nama}
                </h1>
                <div className="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                    <Button asChild>
                        <Link href={edit(tempatWisata.id)}>Edit</Link>
                    </Button>
                    <DeleteTempatWisataDialog tempatWisata={tempatWisata} />
                </div>
            </div>
            <p>
                Destinasi:{' '}
                <Link
                    className="underline"
                    href={showDestinasi(tempatWisata.destinasi_id)}
                >
                    {tempatWisata.destinasi.nama}
                </Link>
            </p>
            {tempatWisata.foto_utama_url ? (
                <img
                    src={tempatWisata.foto_utama_url}
                    alt={tempatWisata.nama}
                    className="max-h-96 w-full rounded-xl border object-contain"
                />
            ) : (
                <p className="text-muted-foreground rounded-xl border p-8 text-center">
                    Belum ada foto utama.
                </p>
            )}
            <div className="flex flex-wrap gap-2">
                <Badge variant={tempatWisata.status ? 'default' : 'secondary'}>
                    {tempatWisata.status ? 'Aktif' : 'Nonaktif'}
                </Badge>
                <Badge variant="outline">
                    {tempatWisata.unggulan ? 'Unggulan' : 'Bukan unggulan'}
                </Badge>
                <Badge variant="outline">Urutan: {tempatWisata.urutan}</Badge>
            </div>
            <dl className="space-y-6">
                <div>
                    <dt className="font-medium">Slug</dt>
                    <dd className="text-muted-foreground break-words">
                        {tempatWisata.slug}
                    </dd>
                </div>
                <div>
                    <dt className="font-medium">Lokasi</dt>
                    <dd className="text-muted-foreground break-words whitespace-pre-wrap">
                        {tempatWisata.lokasi || '—'}
                    </dd>
                </div>
                <div>
                    <dt className="font-medium">Harga tiket</dt>
                    <dd className="text-muted-foreground">
                        {tempatWisata.harga_tiket === null
                            ? 'Belum tersedia'
                            : new Intl.NumberFormat('id-ID', {
                                  style: 'currency',
                                  currency: 'IDR',
                              }).format(Number(tempatWisata.harga_tiket))}
                    </dd>
                </div>
                {wisataTextFields.map((field) => (
                    <div key={field.name}>
                        <dt className="font-medium">{field.label}</dt>
                        <dd className="text-muted-foreground mt-1 break-words whitespace-pre-wrap">
                            {tempatWisata[field.name] || '—'}
                        </dd>
                    </div>
                ))}
                {wisataRelations.map((relation) => (
                    <div key={relation.name}>
                        <dt className="font-medium">{relation.label}</dt>
                        <dd className="mt-2 flex flex-wrap gap-2">
                            {tempatWisata[relation.name].length ? (
                                tempatWisata[relation.name].map((option) => (
                                    <Badge key={option.id} variant="secondary">
                                        {option.nama}
                                    </Badge>
                                ))
                            ) : (
                                <span className="text-muted-foreground">
                                    Belum dipilih
                                </span>
                            )}
                        </dd>
                    </div>
                ))}
            </dl>
            <section className="space-y-3">
                <h2 className="text-lg font-semibold">Galeri foto</h2>
                {tempatWisata.galeri.length ? (
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        {tempatWisata.galeri.map((photo, i) => (
                            <img
                                key={photo.id}
                                src={photo.url}
                                alt={`${tempatWisata.nama} — galeri ${i + 1}`}
                                loading="lazy"
                                className="aspect-square w-full rounded-lg border object-cover"
                            />
                        ))}
                    </div>
                ) : (
                    <p className="text-muted-foreground">
                        Belum ada foto galeri.
                    </p>
                )}
            </section>
            <Button variant="outline" asChild>
                <Link href={index()}>Kembali ke daftar</Link>
            </Button>
        </div>
    );
}
ShowTempatWisata.layout = {
    breadcrumbs: [
        { title: 'Tempat Wisata', href: index() },
        { title: 'Detail', href: '#' },
    ],
};
