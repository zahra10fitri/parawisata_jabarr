import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type MasterItem = {
    id: number;
    nama: string;
    slug: string;
    status: boolean;
    tempat_wisata_count: number;
    inquiry_count?: number;
    deskripsi?: string | null;
    ikon_atau_gambar?: string | null;
    foto_url?: string | null;
};

type PaginatedItems = {
    data: MasterItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

type Config = {
    title: string;
    singular: string;
    path: string;
    description: string;
    hasDetails?: boolean;
    hasImageUpload?: boolean;
};

export default function MasterDataPage({ items, filters, config }: { items: PaginatedItems; filters: { search: string }; config: Config }) {
    const [search, setSearch] = useState(filters.search);
    const [showCreate, setShowCreate] = useState(false);
    const { errors } = usePage().props;

    function submitSearch(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(config.path, { search });
    }

    return <div className="space-y-6 p-4 md:p-6">
        <Head title={config.title}/>
        <div className="flex flex-wrap items-start justify-between gap-4"><div><h1 className="text-2xl font-semibold">{config.title}</h1><p className="text-muted-foreground mt-1 text-sm">{config.description}</p></div><Button onClick={() => setShowCreate(value => !value)}><Plus/>{showCreate ? 'Tutup form' : `Tambah ${config.singular.toLowerCase()}`}</Button></div>
        {showCreate && <MasterForm config={config} onDone={() => setShowCreate(false)}/>} 
        <form onSubmit={submitSearch} className="flex flex-wrap gap-2"><Input aria-label={`Cari ${config.title}`} className="w-full sm:max-w-sm" maxLength={255} placeholder={`Cari nama ${config.singular.toLowerCase()}...`} value={search} onChange={event => setSearch(event.target.value)}/><Button><Search/>Cari</Button><Button variant="outline" asChild><Link href={config.path}><X/>Reset</Link></Button></form>
        <InputError message={(errors as Record<string, string>)[config.singular.toLowerCase()]}/>
        {items.data.length === 0 ? <div className="text-muted-foreground rounded-xl border border-dashed p-10 text-center">{filters.search ? `Tidak ada ${config.singular.toLowerCase()} yang cocok.` : `Belum ada data ${config.singular.toLowerCase()}.`}</div> : <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">{items.data.map(item => <MasterCard key={item.id} item={item} config={config}/>)}</div>}
        <div className="flex flex-wrap items-center justify-between gap-4"><p className="text-muted-foreground text-sm">Menampilkan {items.from ?? 0}–{items.to ?? 0} dari {items.total} data.</p><Pagination currentPage={items.current_page} lastPage={items.last_page} previousUrl={items.prev_page_url} nextUrl={items.next_page_url}/></div>
    </div>;
}

function MasterCard({ item, config }: { item: MasterItem; config: Config }) {
    const [editing, setEditing] = useState(false);
    const usage = item.tempat_wisata_count + (item.inquiry_count ?? 0);

    if (editing) {
        return <MasterForm item={item} config={config} onDone={() => setEditing(false)}/>;
    }

    return <article className="rounded-xl border bg-card p-5 shadow-sm"><div className="flex items-start justify-between gap-3">{config.hasImageUpload && <div className="size-14 shrink-0 overflow-hidden rounded-lg bg-muted">{item.foto_url && <img src={item.foto_url} alt="" className="h-full w-full object-cover"/>}</div>}<div className="min-w-0 flex-1"><h2 className="font-semibold break-words">{item.nama}</h2><p className="text-muted-foreground mt-1 truncate text-xs">/{item.slug}</p></div><Badge variant={item.status ? 'default' : 'secondary'}>{item.status ? 'Aktif' : 'Nonaktif'}</Badge></div>{item.deskripsi && <p className="text-muted-foreground mt-4 line-clamp-2 text-sm">{item.deskripsi}</p>}<div className="text-muted-foreground mt-4 text-sm"><p>{item.tempat_wisata_count} Tempat Wisata terkait</p>{item.inquiry_count !== undefined && <p>{item.inquiry_count} permintaan terkait</p>}</div><div className="mt-5 flex flex-wrap gap-2"><Button size="sm" variant="outline" onClick={() => setEditing(true)}><Pencil/>Edit</Button><Button size="sm" variant="destructive" disabled={usage > 0} title={usage > 0 ? 'Data yang masih digunakan tidak dapat dihapus' : undefined} onClick={() => confirm(`Hapus ${config.singular.toLowerCase()} “${item.nama}”?`) && router.delete(`${config.path}/${item.id}`, { preserveScroll: true })}><Trash2/>Hapus</Button></div></article>;
}

function MasterForm({ item, config, onDone }: { item?: MasterItem; config: Config; onDone: () => void }) {
    const form = useForm<any>({ nama: item?.nama ?? '', status: item?.status ?? true, deskripsi: item?.deskripsi ?? '', ikon_atau_gambar: null });
    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => { if (!item) form.reset(); onDone(); } };
        form.transform((data) => ({ ...data, ...(item ? { _method: 'put' } : {}) }));
        form.post(item ? `${config.path}/${item.id}` : config.path, { ...options, forceFormData: true });
    }

    return <form onSubmit={submit} className="rounded-xl border bg-card p-5 shadow-sm"><div className="grid gap-4"><label><Label>Nama *</Label><Input className="mt-2" required maxLength={255} value={form.data.nama} onChange={event => form.setData('nama', event.target.value)}/><InputError message={form.errors.nama}/></label>{config.hasDetails && <><label><Label>Deskripsi</Label><textarea className="border-input mt-2 min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={form.data.deskripsi} onChange={event => form.setData('deskripsi', event.target.value)}/><InputError message={form.errors.deskripsi}/></label>{config.hasImageUpload ? <label><Label>Foto</Label>{item?.foto_url && !form.data.ikon_atau_gambar && <img src={item.foto_url} alt="Preview foto pengalaman" className="mt-2 h-40 w-full rounded-lg border object-cover"/>}<Input className="mt-2" type="file" accept="image/jpeg,image/png,image/webp" onChange={event => form.setData('ikon_atau_gambar', event.target.files?.[0] ?? null)}/><InputError message={form.errors.ikon_atau_gambar}/></label> : <label><Label>Ikon atau referensi gambar</Label><Input className="mt-2" maxLength={255} value={item?.ikon_atau_gambar ?? ''} disabled/></label>}</>}<label className="flex items-center gap-2 text-sm font-medium"><input type="checkbox" checked={form.data.status} onChange={event => form.setData('status', event.target.checked)}/> Aktif</label><InputError message={form.errors.status}/><div className="flex flex-wrap gap-2"><Button disabled={form.processing}>{form.processing ? 'Menyimpan...' : item ? 'Simpan perubahan' : 'Tambah data'}</Button>{item && <Button type="button" variant="outline" onClick={onDone}>Batal</Button>}</div></div></form>;
}
