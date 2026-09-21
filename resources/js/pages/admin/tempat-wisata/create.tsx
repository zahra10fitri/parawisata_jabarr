import { Head } from '@inertiajs/react';
import TempatWisataForm from '@/components/tempat-wisata-form';
import { index } from '@/routes/admin/tempat-wisata';
import type { WisataOptions } from '@/types/tempat-wisata';

export default function CreateTempatWisata({
    options,
}: {
    options: WisataOptions;
}) {
    return (
        <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <Head title="Tambah Tempat Wisata" />
            <h1 className="text-2xl font-semibold">Tambah tempat wisata</h1>
            <TempatWisataForm options={options} />
        </div>
    );
}
CreateTempatWisata.layout = {
    breadcrumbs: [
        { title: 'Tempat Wisata', href: index() },
        { title: 'Tambah', href: '#' },
    ],
};
