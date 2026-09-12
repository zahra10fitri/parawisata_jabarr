import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import DeleteDestinasiDialog from '@/components/delete-destinasi-dialog';
import InputError from '@/components/input-error';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { create, edit, index, show } from '@/routes/admin/destinasi';
import type { PaginatedDestinasi } from '@/types/destinasi';

export default function IndexDestinasi({
    destinasi,
    filters,
}: {
    destinasi: PaginatedDestinasi;
    filters: { search: string };
}) {
    const [search, setSearch] = useState(filters.search);
    const [searching, setSearching] = useState(false);
    const { errors } = usePage().props;

    function submitSearch(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(
            index.url(),
            { search },
            {
                preserveState: false,
                onStart: () => setSearching(true),
                onFinish: () => setSearching(false),
            },
        );
    }

    return (
        <div className="space-y-6 p-4 md:p-6">
            <Head title="Destinasi" />
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-semibold">Destinasi</h1>
                    <p className="text-muted-foreground text-sm">
                        Kelola destinasi Jelajah Jabar.
                    </p>
                </div>
                <Button asChild>
                    <Link href={create()}>Tambah destinasi</Link>
                </Button>
            </div>
            <form onSubmit={submitSearch} className="flex flex-wrap gap-2">
                <Input
                    aria-label="Cari nama destinasi"
                    placeholder="Cari nama destinasi..."
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    maxLength={255}
                    className="w-full sm:max-w-sm"
                />
                <Button type="submit" disabled={searching}>
                    {searching ? 'Mencari...' : 'Cari'}
                </Button>
                <Button variant="outline" asChild>
                    <Link href={index()}>Reset</Link>
                </Button>
                <InputError message={errors.search} />
            </form>
            {destinasi.data.length === 0 ? (
                <div className="text-muted-foreground rounded-xl border border-dashed p-10 text-center">
                    {filters.search
                        ? 'Tidak ada destinasi yang cocok dengan pencarian.'
                        : 'Belum ada destinasi. Tambahkan destinasi pertama Anda.'}
                </div>
            ) : (
                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted">
                            <tr>
                                {[
                                    'Destinasi',
                                    'Lokasi',
                                    'Status',
                                    'Unggulan',
                                    'Urutan',
                                    'Aksi',
                                ].map((label) => (
                                    <th
                                        key={label}
                                        scope="col"
                                        className="p-4 font-medium whitespace-nowrap"
                                    >
                                        {label}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {destinasi.data.map((item) => (
                                <tr key={item.id} className="border-t">
                                    <td className="p-4">
                                        <div className="flex min-w-48 items-center gap-3">
                                            {item.foto_utama_url ? (
                                                <img
                                                    src={item.foto_utama_url}
                                                    alt=""
                                                    loading="lazy"
                                                    className="size-12 shrink-0 rounded-md object-cover"
                                                />
                                            ) : (
                                                <span className="bg-muted text-muted-foreground flex size-12 shrink-0 items-center justify-center rounded-md text-xs">
                                                    Foto
                                                </span>
                                            )}
                                            <Link
                                                href={show(item.id)}
                                                className="max-w-xs font-medium break-words hover:underline"
                                            >
                                                {item.nama}
                                            </Link>
                                        </div>
                                    </td>
                                    <td className="min-w-36 p-4">
                                        {item.lokasi || '—'}
                                    </td>
                                    <td className="p-4">
                                        <Badge
                                            variant={
                                                item.status
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {item.status ? 'Aktif' : 'Nonaktif'}
                                        </Badge>
                                    </td>
                                    <td className="p-4">
                                        {item.unggulan ? 'Ya' : 'Tidak'}
                                    </td>
                                    <td className="p-4">{item.urutan}</td>
                                    <td className="p-4">
                                        <div className="flex items-center gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link href={show(item.id)}>
                                                    Detail
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link href={edit(item.id)}>
                                                    Edit
                                                </Link>
                                            </Button>
                                            <DeleteDestinasiDialog
                                                destinasi={item}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            <p className="text-muted-foreground text-sm">
                Menampilkan {destinasi.from ?? 0}–{destinasi.to ?? 0} dari{' '}
                {destinasi.total} destinasi.
            </p>
            <Pagination
                currentPage={destinasi.current_page}
                lastPage={destinasi.last_page}
                previousUrl={destinasi.prev_page_url}
                nextUrl={destinasi.next_page_url}
            />
        </div>
    );
}
IndexDestinasi.layout = {
    breadcrumbs: [{ title: 'Destinasi', href: index() }],
};
