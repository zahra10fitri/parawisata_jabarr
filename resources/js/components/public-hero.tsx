import type { ReactNode } from 'react';

export default function PublicHero({ title, description, imageUrl, eyebrow, children }: { title: string; description: string; imageUrl?: string | null; eyebrow?: string; children?: ReactNode }) {
    return <section className="relative isolate min-h-[300px] overflow-hidden bg-[#123f2c] text-white sm:min-h-[360px]">
        {imageUrl && <img src={imageUrl} alt="" className="absolute inset-0 -z-20 size-full object-cover" />}
        <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(5,44,29,.94),rgba(11,58,40,.72),rgba(11,58,40,.25))]" />
        <div className="mx-auto flex min-h-[300px] max-w-[1440px] items-end px-5 py-12 sm:min-h-[360px] sm:px-7 sm:py-16 lg:px-10"><div className="max-w-3xl"><p className="text-xs font-semibold uppercase tracking-[.22em] text-[#c9e7bb]">{eyebrow ?? 'Jelajah Jabar'}</p><h1 className="mt-4 font-serif text-4xl font-semibold leading-tight sm:text-5xl lg:text-6xl">{title}</h1><p className="mt-4 max-w-2xl text-base leading-7 text-white/80 sm:text-lg">{description}</p>{children}</div></div>
    </section>;
}
