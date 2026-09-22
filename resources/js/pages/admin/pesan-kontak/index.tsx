import { Head, Link, router } from '@inertiajs/react';
import { Mail, MailOpen } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Message = { id: number; nama: string; email: string; whatsapp: string | null; subjek: string; pesan: string; dibaca_pada: string | null; created_at: string };
type Page = { data: Message[]; current_page: number; last_page: number; total: number; from: number | null; to: number | null; prev_page_url: string | null; next_page_url: string | null };

export default function Index({ pesanKontak, filters }: { pesanKontak: Page; filters: { search: string; status: string } }) {
    const [search, setSearch] = useState(filters.search); const [status, setStatus] = useState(filters.status);
    const submit = (event: FormEvent) => { event.preventDefault(); router.get('/admin/pesan-kontak', { search, status }, { preserveState: false }); };
    return <div className="space-y-6 p-4 md:p-6"><Head title="Pesan Kontak"/><div><h1 className="text-2xl font-semibold">Pesan Kontak</h1><p className="text-muted-foreground text-sm">Kelola pesan yang masuk dari halaman kontak publik.</p></div><form onSubmit={submit} className="flex flex-wrap gap-2"><Input className="w-full sm:max-w-sm" placeholder="Cari nama, email, subjek, atau pesan..." value={search} onChange={event => setSearch(event.target.value)}/><select className="border-input bg-background h-9 w-full rounded-md border px-3 text-sm sm:max-w-xs" value={status} onChange={event => setStatus(event.target.value)}><option value="">Semua status</option><option value="belum_dibaca">Belum dibaca</option><option value="sudah_dibaca">Sudah dibaca</option></select><Button>Cari</Button><Button variant="outline" asChild><Link href="/admin/pesan-kontak">Reset</Link></Button></form>
        {pesanKontak.data.length === 0 ? <p className="text-muted-foreground rounded-xl border border-dashed p-10 text-center">Tidak ada pesan kontak yang cocok.</p> : <div className="overflow-x-auto rounded-xl border"><table className="w-full text-left text-sm"><thead className="bg-muted"><tr>{['Status', 'Pengirim', 'Subjek', 'Waktu masuk', 'Aksi'].map(label => <th key={label} className="p-4 font-medium whitespace-nowrap">{label}</th>)}</tr></thead><tbody>{pesanKontak.data.map(message => <tr key={message.id} className={`border-t ${message.dibaca_pada ? '' : 'bg-primary/[.035]'}`}><td className="p-4">{message.dibaca_pada ? <Badge variant="secondary" className="gap-1"><MailOpen size={13}/> Dibaca</Badge> : <Badge className="gap-1"><Mail size={13}/> Baru</Badge>}</td><td className="p-4"><p className={message.dibaca_pada ? '' : 'font-semibold'}>{message.nama}</p><p className="text-muted-foreground">{message.email}</p></td><td className="min-w-64 p-4"><p className={message.dibaca_pada ? '' : 'font-semibold'}>{message.subjek}</p><p className="text-muted-foreground mt-1 line-clamp-1">{message.pesan}</p></td><td className="p-4 whitespace-nowrap">{new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(message.created_at))}</td><td className="p-4"><Button size="sm" variant="outline" asChild><Link href={`/admin/pesan-kontak/${message.id}`}>Detail</Link></Button></td></tr>)}</tbody></table></div>}
        <p className="text-muted-foreground text-sm">Menampilkan {pesanKontak.from ?? 0}–{pesanKontak.to ?? 0} dari {pesanKontak.total} pesan.</p><Pagination currentPage={pesanKontak.current_page} lastPage={pesanKontak.last_page} previousUrl={pesanKontak.prev_page_url} nextUrl={pesanKontak.next_page_url}/></div>;
}
