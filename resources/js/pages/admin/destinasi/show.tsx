import { Head, Link } from '@inertiajs/react';
import DeleteDestinasiDialog from '@/components/delete-destinasi-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit, index } from '@/routes/admin/destinasi';
import { destinasiTextFields } from '@/types/destinasi';
import type { Destinasi } from '@/types/destinasi';

export default function ShowDestinasi({ destinasi }: { destinasi: Destinasi }) {
    return (
        <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <Head title={destinasi.nama} />
            <div className="flex flex-wrap items-center justify-between gap-4">
                <h1 className="min-w-0 text-2xl font-semibold break-words">
                    {destinasi.nama}
                </h1>
                <div className="flex items-center gap-2">
                    <Button asChild>
                        <Link href={edit(destinasi.id)}>Edit</Link>
                    </Button>
                    <DeleteDestinasiDialog destinasi={destinasi} />
                </div>
            </div>
            {destinasi.foto_utama_url ? (
                <img
                    src={destinasi.foto_utama_url}
                    alt={destinasi.nama}
                    className="max-h-96 w-full rounded-xl border object-contain"
                />
            ) : (
                <p className="text-muted-foreground rounded-xl border p-8 text-center">
                    Belum ada foto utama.
                </p>
            )}
            <div className="flex flex-wrap gap-2">
                <Badge variant={destinasi.status ? 'default' : 'secondary'}>
                    {destinasi.status ? 'Aktif' : 'Nonaktif'}
                </Badge>
                <Badge variant="outline">
                    {destinasi.unggulan ? 'Unggulan' : 'Bukan unggulan'}
                </Badge>
                <Badge variant="outline">Urutan: {destinasi.urutan}</Badge>
            </div>
            <dl className="space-y-6">
                <div>
                    <dt className="font-medium">Slug</dt>
                    <dd className="text-muted-foreground break-words">
                        {destinasi.slug}
                    </dd>
                </div>
                <div>
                    <dt className="font-medium">Lokasi</dt>
                    <dd className="text-muted-foreground break-words whitespace-pre-wrap">
                        {destinasi.lokasi || '—'}
                    </dd>
                </div>
                {destinasiTextFields.map((field) => (
                    <div key={field.name}>
                        <dt className="font-medium">{field.label}</dt>
                        <dd className="text-muted-foreground mt-1 break-words whitespace-pre-wrap">
                            {destinasi[field.name] || '—'}
                        </dd>
                    </div>
                ))}
            </dl>
            <Button variant="outline" asChild>
                <Link href={index()}>Kembali ke daftar</Link>
            </Button>
        </div>
    );
}
ShowDestinasi.layout = {
    breadcrumbs: [
        { title: 'Destinasi', href: index() },
        { title: 'Detail', href: '#' },
    ],
};
