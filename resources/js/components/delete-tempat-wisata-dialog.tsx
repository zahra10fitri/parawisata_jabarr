import { Form } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { destroy } from '@/routes/admin/tempat-wisata';
import type { TempatWisataSummary } from '@/types/tempat-wisata';

export default function DeleteTempatWisataDialog({
    tempatWisata,
}: {
    tempatWisata: TempatWisataSummary;
}) {
    const [open, setOpen] = useState(false);
    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="destructive"
                    size="sm"
                    aria-label={`Hapus ${tempatWisata.nama}`}
                >
                    Hapus
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Hapus tempat wisata?</DialogTitle>
                <DialogDescription>
                    Tempat wisata “{tempatWisata.nama}” beserta foto utama dan
                    seluruh galeri akan dihapus. Tindakan ini tidak dapat
                    dibatalkan.
                </DialogDescription>
                <Form
                    {...destroy.form(tempatWisata.id)}
                    onSuccess={() => setOpen(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            {Object.values(errors).map((error) => (
                                <p
                                    key={error}
                                    role="alert"
                                    className="text-destructive text-sm"
                                >
                                    {error}
                                </p>
                            ))}
                            <DialogFooter className="mt-4 gap-2">
                                <DialogClose asChild>
                                    <Button
                                        variant="outline"
                                        disabled={processing}
                                    >
                                        Batal
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    {processing
                                        ? 'Menghapus...'
                                        : 'Ya, hapus tempat wisata'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
