import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useFlashToast } from '@/hooks/use-flash-toast';
import PublicLayout from '@/layouts/public-layout';

type Option = { id: number; nama: string };
type Package = { id: number; nama: string; slug: string; durasi_hari: number; harga_mulai: string | null };

export default function Create({ selectedPackage, destinasiOptions, pengalamanOptions }: { selectedPackage: Package | null; destinasiOptions: Option[]; pengalamanOptions: Option[] }) {
    useFlashToast();
    const form = useForm({ paket_perjalanan_id: selectedPackage?.id ?? null as number | null, nama: '', email: '', whatsapp: '', tanggal_perjalanan: '', jumlah_traveler: 1, catatan: '', destinasi: [] as number[], pengalaman: [] as number[] });
    const errors = form.errors as Record<string, string | undefined>;
    const toggle = (field: 'destinasi' | 'pengalaman', id: number) => form.setData(field, form.data[field].includes(id) ? form.data[field].filter(value => value !== id) : [...form.data[field], id]);
    const submit = (event: FormEvent) => { event.preventDefault(); form.post('/rencanakan-perjalanan', { preserveScroll: true, onSuccess: () => form.reset('nama', 'email', 'whatsapp', 'tanggal_perjalanan', 'jumlah_traveler', 'catatan', 'destinasi', 'pengalaman') }); };
    return <PublicLayout><Head title={selectedPackage ? `Ajukan ${selectedPackage.nama}` : 'Rencanakan Perjalanan'} /><div className="mx-auto max-w-3xl px-5 py-12"><p className="text-sm font-medium uppercase tracking-widest text-[#587064]">Jelajah Jabar</p><h1 className="mt-3 text-4xl font-semibold">{selectedPackage ? 'Ajukan Perjalanan' : 'Rencanakan Perjalanan'}</h1><p className="mt-3 text-[#587064]">Ceritakan rencana Anda. Tim kami akan menghubungi Anda melalui WhatsApp atau email.</p>
        {selectedPackage && <div className="mt-8 rounded-2xl bg-[#d7e8cc] p-5"><p className="text-sm text-[#587064]">Paket yang dipilih</p><p className="mt-1 text-lg font-semibold">{selectedPackage.nama}</p><p className="mt-1 text-sm">{selectedPackage.durasi_hari} hari{selectedPackage.harga_mulai ? ` · mulai Rp ${Number(selectedPackage.harga_mulai).toLocaleString('id-ID')} / orang` : ''}</p><Link className="mt-3 inline-block text-sm underline" href="/rencanakan-perjalanan">Rencanakan tanpa paket</Link></div>}
        <form onSubmit={submit} className="mt-8 space-y-6 rounded-3xl bg-white p-5 shadow-sm sm:p-8"><div className="grid gap-5 sm:grid-cols-2">{[['nama', 'Nama lengkap', 'text'], ['email', 'Email', 'email'], ['whatsapp', 'Nomor WhatsApp', 'tel'], ['tanggal_perjalanan', 'Tanggal perjalanan', 'date'], ['jumlah_traveler', 'Jumlah traveler', 'number']].map(([field, label, type]) => <label key={field} className="space-y-2"><Label htmlFor={field}>{label} *</Label><Input id={field} type={type} required min={type === 'number' ? 1 : undefined} max={type === 'number' ? 1000 : undefined} value={form.data[field as keyof typeof form.data] as string | number} onChange={event => form.setData(field as 'nama', event.target.value)} /><InputError message={errors[field]} /></label>)}</div>
            {!selectedPackage && <div className="grid gap-5 sm:grid-cols-2">{([['destinasi', 'Destinasi yang diminati', destinasiOptions], ['pengalaman', 'Minat / pengalaman', pengalamanOptions]] as const).map(([field, label, options]) => <fieldset key={field} className="rounded-xl border border-[#193e32]/15 p-4"><legend className="px-1 font-medium">{label}</legend><div className="mt-2 space-y-2">{options.map(option => <label key={option.id} className="flex items-center gap-2 text-sm"><input type="checkbox" className="size-4 accent-[#245b43]" checked={form.data[field].includes(option.id)} onChange={() => toggle(field, option.id)} />{option.nama}</label>)}</div><InputError message={errors[field]} /></fieldset>)}</div>}
            <label className="block space-y-2"><Label htmlFor="catatan">Catatan atau pesan</Label><textarea id="catatan" rows={5} maxLength={10000} className="w-full rounded-xl border border-[#193e32]/20 bg-transparent px-3 py-2 text-sm" placeholder="Preferensi perjalanan, kebutuhan khusus, atau pertanyaan Anda..." value={form.data.catatan} onChange={event => form.setData('catatan', event.target.value)} /><InputError message={errors.catatan} /></label><Button className="w-full rounded-full bg-[#245b43] py-6 text-white hover:bg-[#193e32]" disabled={form.processing}>{form.processing ? 'Mengirim...' : 'Kirim Permintaan Perjalanan'}</Button></form>
    </div></PublicLayout>;
}
