import { Head, Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Kegiatan = { id: number; judul: string; keterangan: string | null; waktu: string | null; urutan: number };
type Hari = { id: number; hari_ke: number; judul: string | null; urutan: number; kegiatan: Kegiatan[] };

function HariForm({ paketId, hari }: { paketId: number; hari: Hari }) {
    const form = useForm({ hari_ke: hari.hari_ke, judul: hari.judul ?? '', urutan: hari.urutan });
    return <form className="flex flex-wrap items-end gap-2" onSubmit={event => { event.preventDefault(); form.put(`/admin/paket-perjalanan/${paketId}/itinerary/${hari.id}`, { preserveScroll: true }); }}><label><Label>Hari ke-</Label><Input className="w-24" type="number" min={1} max={365} required value={form.data.hari_ke} onChange={event => form.setData('hari_ke', Number(event.target.value))} /></label><label><Label>Judul hari</Label><Input className="w-64 max-w-full" value={form.data.judul} onChange={event => form.setData('judul', event.target.value)} /></label><Button size="sm" variant="outline" disabled={form.processing}>Simpan hari</Button>{form.errors.hari_ke && <span className="text-destructive text-sm">{form.errors.hari_ke}</span>}</form>;
}

function KegiatanForm({ paketId, hari, kegiatan }: { paketId: number; hari: Hari; kegiatan?: Kegiatan }) {
    const form = useForm({ judul: kegiatan?.judul ?? '', keterangan: kegiatan?.keterangan ?? '', waktu: kegiatan?.waktu?.slice(0, 5) ?? '', urutan: kegiatan?.urutan ?? hari.kegiatan.length });
    const url = `/admin/paket-perjalanan/${paketId}/itinerary/${hari.id}/kegiatan${kegiatan ? `/${kegiatan.id}` : ''}`;
    const submit = (event: FormEvent) => { event.preventDefault(); kegiatan ? form.put(url, { preserveScroll: true }) : form.post(url, { preserveScroll: true, onSuccess: () => form.reset() }); };
    return <form onSubmit={submit} className="grid gap-2 rounded-lg border bg-background p-3 md:grid-cols-[1fr_130px_auto]">
        <div className="space-y-2"><Input required placeholder="Judul kegiatan" value={form.data.judul} onChange={e => form.setData('judul', e.target.value)} /><textarea className="border-input w-full rounded-md border px-3 py-2 text-sm" rows={2} placeholder="Keterangan opsional" value={form.data.keterangan} onChange={e => form.setData('keterangan', e.target.value)} /></div>
        <Input aria-label="Waktu" type="time" value={form.data.waktu} onChange={e => form.setData('waktu', e.target.value)} />
        <div className="flex gap-2"><Button size="sm" disabled={form.processing}>{kegiatan ? 'Simpan' : 'Tambah'}</Button>{kegiatan && <Button type="button" size="sm" variant="destructive" onClick={() => confirm('Hapus kegiatan?') && router.delete(url, { preserveScroll: true })}>Hapus</Button>}</div>
    </form>;
}

function move(ids: number[], index: number, direction: -1 | 1): number[] { const target = index + direction; if (target < 0 || target >= ids.length) return ids; const result = [...ids]; [result[index], result[target]] = [result[target], result[index]]; return result; }

export default function ItineraryPage({ paketPerjalanan: paket, itinerary }: { paketPerjalanan: { id: number; nama: string; durasi_hari: number }; itinerary: Hari[] }) {
    const dayForm = useForm({ hari_ke: itinerary.length + 1, judul: '', urutan: itinerary.length });
    const submitDay = (event: FormEvent) => { event.preventDefault(); dayForm.post(`/admin/paket-perjalanan/${paket.id}/itinerary`, { preserveScroll: true, onSuccess: () => dayForm.reset('judul') }); };
    const reorderDays = (index: number, direction: -1 | 1) => router.put(`/admin/paket-perjalanan/${paket.id}/itinerary/urutkan/hari`, { ids: move(itinerary.map(h => h.id), index, direction) }, { preserveScroll: true });
    const reorderActivities = (hari: Hari, index: number, direction: -1 | 1) => router.put(`/admin/paket-perjalanan/${paket.id}/itinerary/${hari.id}/kegiatan-urutkan`, { ids: move(hari.kegiatan.map(k => k.id), index, direction) }, { preserveScroll: true });
    return <div className="mx-auto max-w-5xl space-y-6 p-4 md:p-6"><Head title={`Itinerary — ${paket.nama}`} /><div><Link className="text-muted-foreground text-sm hover:underline" href={`/admin/paket-perjalanan/${paket.id}`}>← Kembali ke paket</Link><h1 className="mt-2 text-2xl font-semibold">Itinerary {paket.nama}</h1><p className="text-muted-foreground">Susun hari dan kegiatan perjalanan secara berurutan.</p></div>
        <form onSubmit={submitDay} className="grid gap-3 rounded-xl border p-4 sm:grid-cols-[120px_1fr_auto]"><label><Label>Hari ke-</Label><Input type="number" min={1} max={365} required value={dayForm.data.hari_ke} onChange={e => dayForm.setData('hari_ke', Number(e.target.value))} /></label><label><Label>Judul hari</Label><Input placeholder="Opsional, mis. Eksplorasi Bandung" value={dayForm.data.judul} onChange={e => dayForm.setData('judul', e.target.value)} /></label><Button className="self-end" disabled={dayForm.processing}>Tambah hari</Button></form>
        {dayForm.errors.hari_ke && <p className="text-destructive text-sm">{dayForm.errors.hari_ke}</p>}
        <div className="space-y-5">{itinerary.map((hari, dayIndex) => <section key={hari.id} className="rounded-xl border p-4"><div className="mb-4 flex flex-wrap items-center justify-between gap-3"><div><h2 className="text-lg font-semibold">Hari ke-{hari.hari_ke}{hari.judul ? ` — ${hari.judul}` : ''}</h2><p className="text-muted-foreground text-sm">{hari.kegiatan.length} kegiatan</p></div><div className="flex gap-2"><Button size="sm" variant="outline" disabled={dayIndex === 0} onClick={() => reorderDays(dayIndex, -1)}>Naik</Button><Button size="sm" variant="outline" disabled={dayIndex === itinerary.length - 1} onClick={() => reorderDays(dayIndex, 1)}>Turun</Button><Button size="sm" variant="destructive" onClick={() => confirm('Hapus hari beserta seluruh kegiatannya?') && router.delete(`/admin/paket-perjalanan/${paket.id}/itinerary/${hari.id}`, { preserveScroll: true })}>Hapus hari</Button></div></div><div className="mb-5 rounded-lg bg-muted/40 p-3"><HariForm paketId={paket.id} hari={hari} /></div>
            <div className="space-y-3">{hari.kegiatan.map((kegiatan, activityIndex) => <div key={kegiatan.id} className="space-y-2"><div className="flex gap-2"><Button size="sm" variant="ghost" disabled={activityIndex === 0} onClick={() => reorderActivities(hari, activityIndex, -1)}>↑</Button><Button size="sm" variant="ghost" disabled={activityIndex === hari.kegiatan.length - 1} onClick={() => reorderActivities(hari, activityIndex, 1)}>↓</Button><span className="self-center text-xs text-muted-foreground">Urutan {activityIndex + 1}</span></div><KegiatanForm paketId={paket.id} hari={hari} kegiatan={kegiatan} /></div>)}<div className="pt-2"><p className="mb-2 text-sm font-medium">Tambah kegiatan</p><KegiatanForm paketId={paket.id} hari={hari} /></div></div>
        </section>)}</div>{itinerary.length === 0 && <p className="rounded-xl border border-dashed p-10 text-center text-muted-foreground">Belum ada itinerary.</p>}</div>;
}
