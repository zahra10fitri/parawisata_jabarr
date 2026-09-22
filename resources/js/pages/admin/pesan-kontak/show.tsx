import { Head, Link, router } from '@inertiajs/react';
import { Mail, MailOpen, Trash2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useFlashToast } from '@/hooks/use-flash-toast';

type Message = { id: number; nama: string; email: string; whatsapp: string | null; subjek: string; pesan: string; dibaca_pada: string | null; created_at: string };

export default function Show({ pesanKontak }: { pesanKontak: Message }) {
    useFlashToast();
    const toggle = () => router.patch(`/admin/pesan-kontak/${pesanKontak.id}/status-baca`, {}, { preserveScroll: true });
    const destroy = () => { if (window.confirm('Hapus pesan kontak ini secara permanen?')) router.delete(`/admin/pesan-kontak/${pesanKontak.id}`); };
    return <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6"><Head title={`Pesan ${pesanKontak.nama}`}/><div><Link href="/admin/pesan-kontak" className="text-muted-foreground text-sm hover:underline">← Kembali ke daftar</Link><div className="mt-3 flex flex-wrap items-start justify-between gap-4"><div><h1 className="text-2xl font-semibold">{pesanKontak.subjek}</h1><p className="text-muted-foreground mt-1">Dikirim {new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(pesanKontak.created_at))}</p></div><Badge variant={pesanKontak.dibaca_pada ? 'secondary' : 'default'}>{pesanKontak.dibaca_pada ? 'Sudah dibaca' : 'Belum dibaca'}</Badge></div></div>
        <div className="flex flex-wrap gap-2"><Button variant="outline" onClick={toggle}>{pesanKontak.dibaca_pada ? <Mail className="mr-2" size={16}/> : <MailOpen className="mr-2" size={16}/>}Tandai {pesanKontak.dibaca_pada ? 'belum dibaca' : 'sudah dibaca'}</Button><Button variant="destructive" onClick={destroy}><Trash2 className="mr-2" size={16}/>Hapus pesan</Button></div>
        <section className="rounded-xl border p-5"><h2 className="text-lg font-semibold">Informasi pengirim</h2><dl className="mt-4 grid gap-5 sm:grid-cols-2">{[['Nama', pesanKontak.nama], ['Email', pesanKontak.email], ['WhatsApp', pesanKontak.whatsapp || 'Tidak dicantumkan'], ['Waktu masuk', new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(pesanKontak.created_at))]].map(([label, value]) => <div key={label}><dt className="text-muted-foreground text-sm">{label}</dt><dd className="mt-1 break-words font-medium">{value}</dd></div>)}</dl></section><section className="rounded-xl border p-5"><h2 className="text-lg font-semibold">Pesan</h2><p className="text-muted-foreground mt-4 whitespace-pre-wrap leading-7">{pesanKontak.pesan}</p></section></div>;
}
