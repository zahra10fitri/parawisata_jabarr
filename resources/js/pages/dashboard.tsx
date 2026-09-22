import { Head, Link } from '@inertiajs/react';
import { ArrowRight, FileText, Mail, MailQuestion, Map, MapPin, PackageOpen, Plus } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type Statistics = { destinasi: number; tempat_wisata: number; paket_perjalanan: number; artikel: number; inquiry: number; pesan_kontak: number };
type RecentInquiry = { id: number; nama: string; status: string; created_at: string; tipe: 'Paket' | 'Custom'; paket: string | null };
type RecentMessage = { id: number; nama: string; subjek: string; dibaca_pada: string | null; created_at: string };
type StatusCount = { status: string; total: number };

const dateTime = (value: string) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));

const statusClass = (status: string) => {
    if (status === 'BARU') return 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
    if (status === 'SELESAI' || status === 'PERJALANAN DIKONFIRMASI' || status === 'PEMBAYARAN DIVERIFIKASI') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
    if (status === 'DIBATALKAN' || status === 'DITOLAK') return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    return 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300';
};

export default function Dashboard({ statistics, recentInquiries, recentMessages, inquiryStatusCounts }: { statistics: Statistics; recentInquiries: RecentInquiry[]; recentMessages: RecentMessage[]; inquiryStatusCounts: StatusCount[] }) {
    const cards: Array<{ label: string; value: number; href: string; icon: LucideIcon; tone: string }> = [
        { label: 'Total Destinasi', value: statistics.destinasi, href: '/admin/destinasi', icon: Map, tone: 'bg-emerald-100 text-emerald-700' },
        { label: 'Total Tempat Wisata', value: statistics.tempat_wisata, href: '/admin/tempat-wisata', icon: MapPin, tone: 'bg-cyan-100 text-cyan-700' },
        { label: 'Total Paket Perjalanan', value: statistics.paket_perjalanan, href: '/admin/paket-perjalanan', icon: PackageOpen, tone: 'bg-violet-100 text-violet-700' },
        { label: 'Total Artikel', value: statistics.artikel, href: '/admin/artikel', icon: FileText, tone: 'bg-orange-100 text-orange-700' },
        { label: 'Total Permintaan', value: statistics.inquiry, href: '/admin/permintaan-perjalanan', icon: MailQuestion, tone: 'bg-blue-100 text-blue-700' },
        { label: 'Total Pesan Kontak', value: statistics.pesan_kontak, href: '/admin/pesan-kontak', icon: Mail, tone: 'bg-rose-100 text-rose-700' },
    ];
    const actions = [
        ['Kelola Destinasi', '/admin/destinasi'], ['Kelola Tempat Wisata', '/admin/tempat-wisata'], ['Kelola Paket Perjalanan', '/admin/paket-perjalanan'], ['Kelola Artikel', '/admin/artikel'], ['Permintaan Perjalanan', '/admin/permintaan-perjalanan'], ['Pesan Kontak', '/admin/pesan-kontak'],
    ];

    return <><Head title="Dashboard"/><div className="flex flex-1 flex-col gap-6 overflow-x-hidden p-4 md:p-6">
        <div><p className="text-muted-foreground text-sm font-medium">Ringkasan Jelajah Jabar</p><h1 className="mt-1 text-2xl font-semibold tracking-tight md:text-3xl">Dashboard Admin</h1><p className="text-muted-foreground mt-2 text-sm">Pantau konten, permintaan perjalanan, dan pesan terbaru dalam satu tampilan.</p></div>
        <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">{cards.map(({ label, value, href, icon: Icon, tone }) => <Link key={label} href={href} className="group rounded-xl border bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><div className="flex items-start justify-between"><span className={`flex size-11 items-center justify-center rounded-xl ${tone}`}><Icon size={21}/></span><ArrowRight size={17} className="text-muted-foreground transition group-hover:translate-x-1"/></div><p className="mt-6 text-3xl font-semibold tabular-nums">{value.toLocaleString('id-ID')}</p><p className="text-muted-foreground mt-1 text-sm">{label}</p></Link>)}</section>
        <div className="grid gap-6 xl:grid-cols-[1.45fr_.8fr]">
            <section className="rounded-xl border bg-card shadow-sm"><div className="flex items-center justify-between gap-4 border-b p-5"><div><h2 className="font-semibold">Permintaan Perjalanan Terbaru</h2><p className="text-muted-foreground mt-1 text-sm">Lima inquiry terbaru yang masuk.</p></div><Button variant="ghost" size="sm" asChild><Link href="/admin/permintaan-perjalanan">Lihat Semua <ArrowRight className="ml-1" size={15}/></Link></Button></div>{recentInquiries.length === 0 ? <p className="text-muted-foreground p-10 text-center text-sm">Belum ada permintaan perjalanan.</p> : <div className="divide-y">{recentInquiries.map(inquiry => <Link key={inquiry.id} href={`/admin/permintaan-perjalanan/${inquiry.id}`} className="flex flex-col gap-3 p-5 transition hover:bg-muted/40 sm:flex-row sm:items-center sm:justify-between"><div className="min-w-0"><p className="font-medium">{inquiry.nama}</p><p className="text-muted-foreground mt-1 truncate text-sm">{inquiry.tipe === 'Paket' ? `Paket · ${inquiry.paket}` : 'Perjalanan custom'}</p></div><div className="flex shrink-0 items-center gap-3 sm:text-right"><div><Badge variant="secondary" className={statusClass(inquiry.status)}>{inquiry.status}</Badge><p className="text-muted-foreground mt-1.5 text-xs">{dateTime(inquiry.created_at)}</p></div><ArrowRight className="text-muted-foreground" size={16}/></div></Link>)}</div>}</section>
            <section className="rounded-xl border bg-card p-5 shadow-sm"><div><h2 className="font-semibold">Inquiry per Status</h2><p className="text-muted-foreground mt-1 text-sm">Distribusi seluruh permintaan.</p></div><div className="mt-5 space-y-2.5">{inquiryStatusCounts.map(item => <div key={item.status} className="flex items-center justify-between gap-3 rounded-lg bg-muted/50 px-3 py-2.5"><span className="text-xs font-medium">{item.status}</span><span className="rounded-md bg-background px-2 py-0.5 text-sm font-semibold tabular-nums shadow-sm">{item.total}</span></div>)}</div></section>
        </div>
        <div className="grid gap-6 xl:grid-cols-[1.45fr_.8fr]">
            <section className="rounded-xl border bg-card shadow-sm"><div className="flex items-center justify-between gap-4 border-b p-5"><div><h2 className="font-semibold">Pesan Kontak Terbaru</h2><p className="text-muted-foreground mt-1 text-sm">Pesan terbaru dari pengunjung.</p></div><Button variant="ghost" size="sm" asChild><Link href="/admin/pesan-kontak">Lihat Semua <ArrowRight className="ml-1" size={15}/></Link></Button></div>{recentMessages.length === 0 ? <p className="text-muted-foreground p-10 text-center text-sm">Belum ada pesan kontak.</p> : <div className="divide-y">{recentMessages.map(message => <Link key={message.id} href={`/admin/pesan-kontak/${message.id}`} className="flex items-center justify-between gap-4 p-5 transition hover:bg-muted/40"><div className="min-w-0"><div className="flex items-center gap-2"><p className={`truncate ${message.dibaca_pada ? 'font-medium' : 'font-semibold'}`}>{message.nama}</p>{!message.dibaca_pada && <span className="size-2 shrink-0 rounded-full bg-blue-500" aria-label="Belum dibaca"/>}</div><p className="text-muted-foreground mt-1 truncate text-sm">{message.subjek}</p><p className="text-muted-foreground mt-1 text-xs">{dateTime(message.created_at)}</p></div><Badge variant={message.dibaca_pada ? 'secondary' : 'default'}>{message.dibaca_pada ? 'Dibaca' : 'Baru'}</Badge></Link>)}</div>}</section>
            <section className="rounded-xl border bg-card p-5 shadow-sm"><h2 className="font-semibold">Quick Action</h2><p className="text-muted-foreground mt-1 text-sm">Buka modul pengelolaan dengan cepat.</p><div className="mt-5 grid gap-2">{actions.map(([label, href]) => <Button key={href} variant="outline" className="h-auto justify-between px-3 py-3 font-normal" asChild><Link href={href}><span className="flex items-center gap-2"><Plus size={15}/>{label}</span><ArrowRight size={15}/></Link></Button>)}</div></section>
        </div>
    </div></>;
}

Dashboard.layout = { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] };
