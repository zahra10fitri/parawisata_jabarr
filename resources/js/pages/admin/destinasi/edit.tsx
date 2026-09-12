import { Head } from '@inertiajs/react';
import DestinasiForm from '@/components/destinasi-form';
import { index } from '@/routes/admin/destinasi';
import type { Destinasi } from '@/types/destinasi';

export default function EditDestinasi({ destinasi }: { destinasi: Destinasi }) {
    return (
        <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <Head title={`Edit ${destinasi.nama}`} />
            <h1 className="text-2xl font-semibold">Edit destinasi</h1>
            <DestinasiForm key={destinasi.id} destinasi={destinasi} />
        </div>
    );
}
EditDestinasi.layout = {
    breadcrumbs: [
        { title: 'Destinasi', href: index() },
        { title: 'Edit', href: '#' },
    ],
};
