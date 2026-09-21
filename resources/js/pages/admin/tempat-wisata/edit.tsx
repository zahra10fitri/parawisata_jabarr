import { Head } from '@inertiajs/react';
import TempatWisataForm from '@/components/tempat-wisata-form';
import { index } from '@/routes/admin/tempat-wisata';
import type { TempatWisata, WisataOptions } from '@/types/tempat-wisata';

export default function EditTempatWisata({
    tempatWisata,
    options,
}: {
    tempatWisata: TempatWisata;
    options: WisataOptions;
}) {
    return (
        <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <Head title={`Edit ${tempatWisata.nama}`} />
            <h1 className="text-2xl font-semibold">Edit tempat wisata</h1>
            <TempatWisataForm
                key={tempatWisata.id}
                tempatWisata={tempatWisata}
                options={options}
            />
        </div>
    );
}
EditTempatWisata.layout = {
    breadcrumbs: [
        { title: 'Tempat Wisata', href: index() },
        { title: 'Edit', href: '#' },
    ],
};
