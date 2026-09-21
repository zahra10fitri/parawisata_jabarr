import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import DeleteTempatWisataDialog from '@/components/delete-tempat-wisata-dialog';
import InputError from '@/components/input-error';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { create, edit, index, show } from '@/routes/admin/tempat-wisata';
import type {
    MasterOption,
    PaginatedTempatWisata,
} from '@/types/tempat-wisata';

export default function IndexTempatWisata({
    tempatWisata,
    filters,
    destinasiOptions,
}: {
    tempatWisata: PaginatedTempatWisata;
    filters: { search: string; destinasi_id: string };
    destinasiOptions: MasterOption[];
}) {
    const [search, setSearch] = useState(filters.search);
    const [destinasiId, setDestinasiId] = useState(filters.destinasi_id);
    const [searching, setSearching] = useState(false);
    const { errors } = usePage().props;

    function submitSearch(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(
            index.url(),
            { search, destinasi_id: destinasiId },
            {
                preserveState: false,
                onStart: () => setSearching(true),
                onFinish: () => setSearching(false),
            },
        );
    }

    return (
        <div className="space-y-6 p-4 md:p-6">
            <Head title="Tempat Wisata" />
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-semibold">Tempat Wisata</h1>
                    <p className="text-muted-foreground text-sm">
                        Kelola tempat wisata Jelajah Jabar.
                    </p>
                </div>
                <Button asChild>
                    <Link href={create()}>Tambah tempat wisata</Link>
                </Button>
            </div>
            <form onSubmit={submitSearch} className="flex flex-wrap gap-2">
                <Input
                    aria-label="Cari nama tempat wisata"
                    placeholder="Cari nama tempat wisata..."
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    maxLength={255}
                    className="w-full sm:max-w-sm"
                />
                <select
                    aria-label="Filter Destinasi"
                    value={destinasiId}
                    onChange={(event) => setDestinasiId(event.target.value)}
                    className="border-input bg-background h-9 w-full rounded-md border px-3 text-sm sm:max-w-xs"
                >
                    <option value="">Semua Destinasi</option>
                    {destinasiOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.nama}
                        </option>
                    ))}
                </select>
                <Button type="submit" disabled={searching}>
                    {searching ? 'Mencari...' : 'Cari'}
                </Button>
                <Button variant="outline" asChild>
                    <Link href={index()}>Reset</Link>
                </Button>
                <InputError message={errors.search} />
                <InputError message={errors.destinasi_id} />
            </form>
            {tempatWisata.data.length === 0 ? (
                <div className="text-muted-foreground rounded-xl border border-dashed p-10 text-center">
                    {filters.search || filters.destinasi_id
                        ? 'Tidak ada tempat wisata yang cocok dengan pencarian atau filter.'
                        : 'Belum ada tempat wisata. Tambahkan tempat wisata pertama Anda.'}
                </div>
            ) : (
                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted">
                            <tr>
                                {[
                                    'Tempat Wisata',
                                    'Destinasi',
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
                            {tempatWisata.data.map((item) => (
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
                                        {item.destinasi.nama}
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
                                            <DeleteTempatWisataDialog
                                                tempatWisata={item}
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
                Menampilkan {tempatWisata.from ?? 0}–{tempatWisata.to ?? 0} dari{' '}
                {tempatWisata.total} tempat wisata.
            </p>
            <Pagination
                currentPage={tempatWisata.current_page}
                lastPage={tempatWisata.last_page}
                previousUrl={tempatWisata.prev_page_url}
                nextUrl={tempatWisata.next_page_url}
            />
        </div>
    );
}
IndexTempatWisata.layout = {
    breadcrumbs: [{ title: 'Tempat Wisata', href: index() }],
};
