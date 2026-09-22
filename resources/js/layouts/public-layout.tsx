import { Link } from '@inertiajs/react';
import { ArrowRight, Instagram, Leaf, Mail, Menu, Search, X, Youtube } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { home } from '@/routes';

export default function PublicLayout({ children }: { children: ReactNode }) {
    const [menuOpen, setMenuOpen] = useState(false);
    const navigation = [['Beranda', home()], ['Jelajahi', '/jelajahi'], ['Paket Perjalanan', '/paket-perjalanan'], ['Inspirasi', '/inspirasi'], ['Tentang', '/tentang'], ['Kontak', '/kontak']] as const;

    return <div data-public-layout className="min-h-screen overflow-x-clip bg-[#fbfaf4] text-[#173c30] scheme-light selection:bg-[#d7e8cc] [&_a:focus-visible]:outline-2 [&_a:focus-visible]:outline-offset-4 [&_button:focus-visible]:outline-2 [&_button:focus-visible]:outline-offset-4">
        <a href="#konten" className="sr-only z-50 bg-white p-4 focus:not-sr-only focus:fixed">Langsung ke konten</a>
        <header className="relative z-40 border-b border-[#173c30]/10 bg-[#fbfaf4]/95 backdrop-blur">
            <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-2 px-4 sm:gap-5 sm:px-8">
                <Link href={home()} className="flex shrink-0 items-center gap-2.5" aria-label="Jelajah Jabar — Beranda"><span className="flex size-10 items-center justify-center rounded-full bg-[#17613f] text-white"><Leaf size={21}/></span><span><strong className="block font-serif text-lg leading-none">Jelajah Jabar</strong><small className="mt-1 hidden text-[9px] uppercase tracking-[.16em] text-[#6b7d72] sm:block">Alam, Budaya, Cerita</small></span></Link>
                <nav aria-label="Navigasi publik" className="hidden items-center gap-6 text-sm font-medium lg:flex">{navigation.map(([label, href]) => <Link key={label} href={href} className="hover:text-[#17613f]">{label}</Link>)}</nav>
                <div className="flex items-center gap-1 sm:gap-2"><Link href="/pencarian" aria-label="Cari seluruh Jelajah Jabar" className="flex size-11 items-center justify-center rounded-full text-[#173c30] transition hover:bg-[#e8eee3]"><Search size={19}/></Link><Link href="/rencanakan-perjalanan" className="hidden items-center gap-2 rounded-xl bg-[#17613f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#104d31] sm:inline-flex">Rencanakan Perjalanan<ArrowRight size={15}/></Link><button type="button" className="flex size-11 items-center justify-center rounded-full transition hover:bg-[#e8eee3] lg:hidden" aria-label={menuOpen ? 'Tutup menu' : 'Buka menu'} aria-expanded={menuOpen} onClick={() => setMenuOpen(value => !value)}>{menuOpen ? <X size={22}/> : <Menu size={22}/>}</button></div>
            </div>
            {menuOpen && <nav aria-label="Navigasi mobile" className="absolute inset-x-0 top-full border-t border-[#173c30]/10 bg-[#fbfaf4] px-4 py-4 shadow-xl lg:hidden"><div className="mx-auto grid max-w-7xl gap-1">{navigation.map(([label, href]) => <Link key={label} href={href} onClick={() => setMenuOpen(false)} className="rounded-xl px-4 py-3 text-sm font-medium hover:bg-[#e8eee3]">{label}</Link>)}<Link href="/rencanakan-perjalanan" onClick={() => setMenuOpen(false)} className="mt-2 flex items-center justify-between rounded-xl bg-[#17613f] px-4 py-3 text-sm font-semibold text-white">Rencanakan Perjalanan <ArrowRight size={16}/></Link></div></nav>}
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
