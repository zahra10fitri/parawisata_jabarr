import { Link } from '@inertiajs/react';
import { ArrowUpRight, Leaf } from 'lucide-react';
import type { ReactNode } from 'react';
import { home } from '@/routes';
import { index } from '@/routes/jelajahi';

export default function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <div className="min-h-screen bg-[#f7f5ee] text-[#193e32] scheme-light selection:bg-[#d7e8cc] [&_a:focus-visible]:outline-2 [&_a:focus-visible]:outline-offset-4 [&_button:focus-visible]:outline-2 [&_button:focus-visible]:outline-offset-4">
            <a
                href="#konten"
                className="sr-only z-50 bg-white p-4 focus:not-sr-only focus:fixed"
            >
                Langsung ke konten
            </a>
            <header className="border-b border-[#193e32]/10">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-5 sm:px-8">
                    <Link
                        href={home()}
                        className="flex items-center gap-2.5"
                        aria-label="Jelajah Jabar — Beranda"
                    >
                        <span className="flex size-10 items-center justify-center rounded-full bg-[#245b43] text-white">
                            <Leaf size={22} aria-hidden="true" />
                        </span>
                        <span className="text-lg font-semibold tracking-tight">
                            jelajah<span className="font-normal">jabar.</span>
                        </span>
                    </Link>
                    <nav
                        aria-label="Navigasi publik"
                        className="flex items-center gap-5 text-sm"
                    >
                        <Link
                            href={home()}
                            className="hidden hover:underline sm:inline"
                        >
                            Beranda
                        </Link>
                        <Link
                            href={index()}
                            className="inline-flex items-center gap-2 rounded-full bg-[#245b43] px-5 py-2.5 font-medium text-white"
                        >
                            Jelajahi{' '}
                            <ArrowUpRight size={16} aria-hidden="true" />
                        </Link>
                        <Link href="/paket-perjalanan" className="hidden hover:underline md:inline">Paket Perjalanan</Link>
                        <Link href="/rencanakan-perjalanan" className="hidden hover:underline lg:inline">Rencanakan Perjalanan</Link>
                    </nav>
                </div>
            </header>
            <main id="konten">{children}</main>
            <footer className="mt-20 border-t border-[#193e32]/15">
                <div className="mx-auto flex max-w-7xl flex-col justify-between gap-5 px-5 py-10 sm:flex-row sm:px-8">
                    <div>
                        <p className="font-semibold">Jelajah Jabar</p>
                        <p className="mt-2 text-sm text-[#587064]">
                            Cerita baru, tak jauh dari sini.
                        </p>
                    </div>
                    <Link
                        href={index()}
                        className="inline-flex items-center gap-2 self-start text-sm font-medium"
                    >
                        Temukan tempat berikutnya{' '}
                        <ArrowUpRight size={16} aria-hidden="true" />
                    </Link>
                </div>
            </footer>
        </div>
    );
}
