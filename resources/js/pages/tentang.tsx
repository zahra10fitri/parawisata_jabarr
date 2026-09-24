import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    Check,
    Compass,
    HeartHandshake,
    Leaf,
    MapPinned,
    Mountain,
    Sparkles,
} from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';

const values = [
    {
        icon: MapPinned,
        title: 'Pilihan yang relevan',
        text: 'Destinasi, wisata, dan inspirasi dirangkai agar perjalanan lebih mudah ditemukan dan direncanakan.',
    },
    {
        icon: HeartHandshake,
        title: 'Dekat dengan cerita lokal',
        text: 'Kami mengangkat alam, budaya, rasa, dan masyarakat yang memberi setiap perjalanan makna.',
    },
    {
        icon: Sparkles,
        title: 'Pengalaman yang personal',
        text: 'Mulai dari paket terkurasi hingga perjalanan custom, setiap rencana dapat mengikuti cara Anda menjelajah.',
    },
];

export default function Tentang({ heroImage }: { heroImage: string | null }) {
    return (
        <PublicLayout>
            <Head title="Tentang Kami" />

            <section className="relative isolate min-h-[470px] overflow-hidden bg-[#123f2c] text-white sm:min-h-[540px]">
                {heroImage && (
                    <img
                        src={heroImage}
                        alt="Pesona alam Jawa Barat"
                        className="absolute inset-0 -z-20 size-full object-cover"
                    />
                )}
                <div className="absolute inset-0 -z-10 bg-gradient-to-r from-[#062a1d]/95 via-[#0b3828]/72 to-[#0b3828]/20" />
                <div className="mx-auto flex min-h-[470px] max-w-[1440px] items-center px-5 py-16 sm:min-h-[540px] sm:px-7 lg:px-10">
                    <div className="max-w-3xl">
                        <p className="flex items-center gap-3 text-xs font-semibold tracking-[.2em] text-[#d1e7c6] uppercase before:h-px before:w-8 before:bg-[#d5c95b]">
                            Tentang Jelajah Jabar
                        </p>
                        <h1 className="mt-6 max-w-2xl font-serif text-5xl leading-[1.05] font-semibold sm:text-6xl lg:text-7xl">
                            Mendekatkan Anda pada cerita terbaik Jawa Barat.
                        </h1>
                        <p className="mt-6 max-w-2xl text-base leading-7 text-white/85 sm:text-lg sm:leading-8">
                            Sebuah platform perjalanan untuk menemukan pesona alam, budaya, kuliner, dan pengalaman lokal—dalam satu ruang yang hangat dan mudah dijelajahi.
                        </p>
                    </div>
                </div>
            </section>

            <section className="mx-auto grid max-w-[1440px] items-center gap-12 px-5 py-16 sm:px-7 sm:py-20 lg:grid-cols-[.9fr_1.1fr] lg:gap-20 lg:px-10 lg:py-24">
                <div>
                    <p className="flex items-center gap-3 text-xs font-semibold tracking-[.17em] text-[#52705f] uppercase before:h-px before:w-8 before:bg-[#17613f]">
                        Siapa kami
                    </p>
                    <h2 className="mt-5 font-serif text-4xl leading-tight font-semibold text-[#173c30] sm:text-5xl">
                        Jawa Barat, dilihat lebih dekat.
                    </h2>
                    <div className="mt-6 space-y-5 text-base leading-8 text-[#587064]">
                        <p>Jelajah Jabar hadir sebagai teman perjalanan digital bagi siapa pun yang ingin mengenal Jawa Barat secara lebih utuh. Kami mempertemukan inspirasi, informasi destinasi, tempat wisata, dan paket perjalanan dalam pengalaman yang sederhana.</p>
                        <p>Kami percaya perjalanan terbaik bukan sekadar berpindah tempat. Ia membuka percakapan, menjaga ingatan, serta menumbuhkan apresiasi terhadap alam dan kearifan lokal.</p>
                    </div>
                    <Link href="/jelajahi" className="mt-8 inline-flex items-center gap-3 rounded-full bg-[#17613f] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-[#0f4d31]">
                        Jelajahi Destinasi <ArrowRight size={17} />
                    </Link>
                </div>
                <div className="relative overflow-hidden rounded-[1.75rem] bg-[#dce8d5] shadow-[0_24px_70px_rgba(23,60,48,.14)]">
                    {heroImage ? (
                        <img src={heroImage} alt="Keindahan Jawa Barat" className="aspect-[4/3] size-full object-cover sm:aspect-[16/11]" />
                    ) : (
                        <div className="flex aspect-[16/11] items-center justify-center bg-gradient-to-br from-[#dce8d5] to-[#91ae91] text-[#17613f]"><Mountain size={68} strokeWidth={1.2} /></div>
                    )}
                    <div className="absolute inset-x-5 bottom-5 rounded-2xl border border-white/40 bg-white/90 p-5 shadow-lg backdrop-blur sm:inset-x-7 sm:bottom-7 sm:p-6">
                        <p className="font-serif text-xl font-semibold text-[#173c30] sm:text-2xl">Alam, budaya, dan cerita dalam satu perjalanan.</p>
                    </div>
                </div>
            </section>

            <section className="relative overflow-hidden bg-[#eef4ed]">
                <div className="absolute -top-32 -left-32 size-80 rounded-full border border-[#17613f]/5" />
                <div className="absolute -right-36 -bottom-40 size-96 rounded-full border border-[#17613f]/5" />
                <div className="relative mx-auto max-w-[1440px] px-5 py-16 sm:px-7 sm:py-20 lg:px-10 lg:py-24">
                    <div className="mx-auto max-w-3xl text-center">
                        <p className="flex items-center justify-center gap-3 text-xs font-semibold tracking-[.17em] text-[#52705f] uppercase before:h-px before:w-8 before:bg-[#17613f]">Tujuan kami</p>
                        <h2 className="mt-5 font-serif text-4xl leading-tight font-semibold text-[#173c30] sm:text-5xl">Membuat perjalanan terasa lebih dekat, jernih, dan bermakna.</h2>
                    </div>
                    <div className="mt-12 grid gap-6 lg:grid-cols-2">
                        <article className="rounded-[1.75rem] border border-[#173c30]/10 bg-white p-7 shadow-[0_16px_50px_rgba(23,60,48,.07)] sm:p-9">
                            <span className="flex size-14 items-center justify-center rounded-full bg-[#dcebd3] text-[#17613f]"><Compass size={27} /></span>
                            <h3 className="mt-6 font-serif text-3xl font-semibold">Kurasi, bukan sekadar katalog</h3>
                            <p className="mt-4 leading-7 text-[#587064]">Informasi disusun untuk membantu Anda menemukan pilihan yang sesuai, bukan membuat Anda tenggelam dalam terlalu banyak pilihan.</p>
                        </article>
                        <article className="rounded-[1.75rem] border border-[#173c30]/10 bg-white p-7 shadow-[0_16px_50px_rgba(23,60,48,.07)] sm:p-9">
                            <span className="flex size-14 items-center justify-center rounded-full bg-[#17613f] text-white"><Leaf size={27} /></span>
                            <h3 className="mt-6 font-serif text-3xl font-semibold">Perjalanan yang berpijak pada cerita lokal</h3>
                            <ul className="mt-5 space-y-3 text-sm leading-6 text-[#587064]">
                                {['Inspirasi yang membumi', 'Rencana yang fleksibel', 'Apresiasi terhadap alam dan kearifan lokal'].map((item) => <li key={item} className="flex items-start gap-3"><span className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-[#dcebd3] text-[#17613f]"><Check size={13} /></span>{item}</li>)}
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-[1440px] px-5 py-16 sm:px-7 sm:py-20 lg:px-10 lg:py-24">
                <div className="mx-auto max-w-3xl text-center">
                    <p className="flex items-center justify-center gap-3 text-xs font-semibold tracking-[.17em] text-[#52705f] uppercase before:h-px before:w-8 before:bg-[#17613f]">Mengapa Jelajah Jabar</p>
                    <h2 className="mt-5 font-serif text-4xl font-semibold text-[#173c30] sm:text-5xl">Dibangun untuk perjalanan yang Anda ingat.</h2>
                </div>
                <div className="mt-12 grid gap-5 md:grid-cols-3">
                    {values.map(({ icon: Icon, title, text }, index) => (
                        <article key={title} className="group rounded-[1.5rem] border border-[#173c30]/10 bg-white p-7 text-center shadow-[0_16px_45px_rgba(23,60,48,.055)] transition hover:-translate-y-1 hover:shadow-[0_22px_55px_rgba(23,60,48,.1)] sm:p-8">
                            <span className={`mx-auto flex size-14 items-center justify-center rounded-full ${index === 1 ? 'bg-[#f4e9c9] text-[#a97814]' : index === 2 ? 'bg-[#e4eafa] text-[#4268ae]' : 'bg-[#dcebd3] text-[#17613f]'}`}><Icon size={25} /></span>
                            <h3 className="mt-6 font-serif text-2xl font-semibold">{title}</h3>
                            <p className="mt-3 text-sm leading-7 text-[#587064]">{text}</p>
                        </article>
                    ))}
                </div>
            </section>

            <section className="relative isolate overflow-hidden bg-[#123f2c] text-white">
                {heroImage && <img src={heroImage} alt="Bentang alam Jawa Barat" className="absolute inset-0 -z-20 size-full object-cover" />}
                <div className="absolute inset-0 -z-10 bg-gradient-to-r from-[#062a1d]/92 via-[#0b3828]/72 to-[#0b3828]/30" />
                <div className="mx-auto max-w-[1440px] px-5 py-20 sm:px-7 lg:px-10 lg:py-24">
                    <div className="max-w-2xl">
                        <p className="flex items-center gap-3 text-xs font-semibold tracking-[.17em] text-[#d1e7c6] uppercase before:h-px before:w-8 before:bg-[#d5c95b]">Jawa Barat, lebih dari sekadar destinasi</p>
                        <h2 className="mt-5 font-serif text-4xl leading-tight font-semibold sm:text-5xl">Alam, budaya, rasa, dan masyarakat yang memberi perjalanan makna.</h2>
                        <p className="mt-5 max-w-xl leading-7 text-white/80">Kenali Jawa Barat melalui pengalaman yang membuka percakapan, menjaga ingatan, dan mempertemukan Anda dengan cerita lokal.</p>
                        <Link href="/jelajahi" className="mt-8 inline-flex items-center gap-3 rounded-full border border-white/70 px-6 py-3.5 text-sm font-semibold transition hover:bg-white hover:text-[#173c30]">Jelajahi Jawa Barat <ArrowRight size={17} /></Link>
                    </div>
                </div>
            </section>

            <section className="relative overflow-hidden bg-[#edf4eb]">
                <div className="absolute -left-20 top-1/2 size-52 -translate-y-1/2 rounded-full bg-[#cfe3c9]/60 blur-3xl" />
                <div className="absolute -right-20 top-1/2 size-52 -translate-y-1/2 rounded-full bg-[#cfe3c9]/60 blur-3xl" />
                <div className="relative mx-auto max-w-[1440px] px-5 py-14 text-center sm:px-7 sm:py-16 lg:px-10">
                    <p className="text-xs font-semibold tracking-[.17em] text-[#52705f] uppercase">Mulai dari sebuah rencana</p>
                    <h2 className="mt-4 font-serif text-4xl font-semibold text-[#173c30] sm:text-5xl">Rencanakan perjalanan versi Anda.</h2>
                    <p className="mx-auto mt-4 max-w-2xl leading-7 text-[#587064]">Temukan destinasi menarik, dapatkan inspirasi, atau ajukan rencana perjalanan yang sesuai dengan cara Anda menikmati Jawa Barat.</p>
                    <Link href="/rencanakan-perjalanan" className="mt-7 inline-flex items-center gap-3 rounded-full bg-[#17613f] px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-[#0f4d31]">Rencanakan Perjalanan <ArrowRight size={17} /></Link>
                </div>
            </section>
        </PublicLayout>
    );
}
