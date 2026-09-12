import { Head } from '@inertiajs/react';
import DestinasiForm from '@/components/destinasi-form';
import { index } from '@/routes/admin/destinasi';

export default function CreateDestinasi() {
    return (
        <div className="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <Head title="Tambah Destinasi" />
            <h1 className="text-2xl font-semibold">Tambah destinasi</h1>
            <DestinasiForm />
        </div>
    );
}
CreateDestinasi.layout = {
    breadcrumbs: [
        { title: 'Destinasi', href: index() },
        { title: 'Tambah', href: '#' },
    ],
};
