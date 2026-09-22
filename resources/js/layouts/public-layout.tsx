import { Link } from '@inertiajs/react';
import { ArrowRight, Instagram, Leaf, Mail, Search, Youtube } from 'lucide-react';
import type { ReactNode } from 'react';
import { home } from '@/routes';

export default function PublicLayout({ children }: { children: ReactNode }) {
    return <div className="min-h-screen bg-[#fbfaf4] text-[#173c30] scheme-light selection:bg-[#d7e8cc] [&_a:focus-visible]:outline-2 [&_a:focus-visible]:outline-offset-4 [&_button:focus-visible]:outline-2 [&_button:focus-visible]:outline-offset-4">
        <a href="#konten" className="sr-only z-50 bg-white p-4 focus:not-sr-only focus:fixed">Langsung ke konten</a>
        <header className="relative z-40 border-b border-[#173c30]/10 bg-[#fbfaf4]/95 backdrop-blur">
            <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-5 sm:px-8">
                <Link href={home()} className="flex shrink-0 items-center gap-2.5" aria-label="Jelajah Jabar — Beranda"><span className="flex size-10 items-center justify-center rounded-full bg-[#17613f] text-white"><Leaf size={21}/></span><span><strong className="block font-serif text-lg leading-none">Jelajah Jabar</strong><small className="mt-1 hidden text-[9px] uppercase tracking-[.16em] text-[#6b7d72] sm:block">Alam, Budaya, Cerita</small></span></Link>
                <nav aria-label="Navigasi publik" className="hidden items-center gap-6 text-sm font-medium lg:flex"><Link href={home()} className="text-[#17613f]">Beranda</Link><Link href="/jelajahi" className="hover:text-[#17613f]">Jelajahi</Link><Link href="/paket-perjalanan" className="hover:text-[#17613f]">Paket Perjalanan</Link><Link href="/inspirasi" className="hover:text-[#17613f]">Inspirasi</Link><Link href="/tentang" className="hover:text-[#17613f]">Tentang</Link><Link href="/kontak" className="hover:text-[#17613f]">Kontak</Link></nav>
                <div className="flex items-center gap-2"><Link href="/pencarian" aria-label="Cari seluruh Jelajah Jabar" className="flex size-10 items-center justify-center rounded-full text-[#173c30] transition hover:bg-[#e8eee3]"><Search size={19}/></Link><Link href="/rencanakan-perjalanan" className="inline-flex items-center gap-2 rounded-xl bg-[#17613f] px-4 py-3 text-xs font-semibold text-white transition hover:bg-[#104d31] sm:px-5 sm:text-sm">Rencanakan <span className="hidden sm:inline">Perjalanan</span><ArrowRight size={15}/></Link></div>
            </div>
        </header>
        <main id="konten">{children}</main>
        <footer className="bg-[#073d27] text-white">
            <div className="mx-auto grid max-w-7xl gap-12 px-6 py-16 sm:px-8 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.5fr]">
                <div><Link href={home()} className="flex items-center gap-3"><span className="flex size-11 items-center justify-center rounded-full bg-white text-[#17613f]"><Leaf size={22}/></span><span className="font-serif text-xl font-semibold">Jelajah Jabar</span></Link><p className="mt-5 max-w-xs text-sm leading-6 text-white/65">Temukan inspirasi, destinasi, dan pengalaman perjalanan terbaik untuk mengenal Jawa Barat lebih dekat.</p><div className="mt-6 flex gap-3"><span className="rounded-full border border-white/20 p-2"><Instagram size={17}/></span><span className="rounded-full border border-white/20 p-2"><Youtube size={17}/></span><span className="rounded-full border border-white/20 p-2"><Mail size={17}/></span></div></div>
                <div><h2 className="font-semibold">Jelajahi</h2><div className="mt-5 space-y-3 text-sm text-white/65"><Link className="block hover:text-white" href="/jelajahi">Destinasi & Wisata</Link><Link className="block hover:text-white" href="/paket-perjalanan">Paket Perjalanan</Link><Link className="block hover:text-white" href="/inspirasi">Inspirasi</Link></div></div>
                <div><h2 className="font-semibold">Tentang kami</h2><div className="mt-5 space-y-3 text-sm text-white/65"><Link className="block hover:text-white" href="/tentang">Tentang Jelajah Jabar</Link><Link className="block hover:text-white" href="/kontak">Kontak</Link><Link className="block hover:text-white" href="/rencanakan-perjalanan">Rencanakan perjalanan</Link></div></div>
                <div><h2 className="font-semibold">Mulai cerita Anda</h2><p className="mt-5 text-sm leading-6 text-white/65">Jelajahi tempat baru dan susun perjalanan yang sesuai dengan cara Anda menikmati Jawa Barat.</p><Link href="/rencanakan-perjalanan" className="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-semibold text-[#073d27]">Mulai rencanakan <ArrowRight size={16}/></Link></div>
            </div>
            <div className="border-t border-white/10"><div className="mx-auto flex max-w-7xl flex-col justify-between gap-2 px-6 py-6 text-xs text-white/45 sm:flex-row sm:px-8"><p>© {new Date().getFullYear()} Jelajah Jabar. Semua hak dilindungi.</p><p>Jelajahi lebih jauh, temukan cerita baru.</p></div></div>
        </footer>
    </div>;
}
