import { Head } from '@inertiajs/react';
import ContactSection from '@/components/contact-section';
import PublicHero from '@/components/public-hero';
import PublicLayout from '@/layouts/public-layout';

export default function Kontak() {
    return <PublicLayout><Head title="Kontak" /><PublicHero eyebrow="Hubungi Kami" title="Mari merencanakan perjalananmu" description="Sampaikan pertanyaan atau rencana perjalananmu kepada tim Jelajah Jabar." /><ContactSection /></PublicLayout>;
}
