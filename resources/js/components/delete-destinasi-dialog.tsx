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
import { destroy } from '@/routes/admin/destinasi';
import type { DestinasiSummary } from '@/types/destinasi';

export default function DeleteDestinasiDialog({
    destinasi,
}: {
    destinasi: DestinasiSummary;
}) {
    const [open, setOpen] = useState(false);
    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="destructive"
                    size="sm"
                    aria-label={`Hapus ${destinasi.nama}`}
                >
                    Hapus
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Hapus destinasi?</DialogTitle>
                <DialogDescription>
                    Destinasi “{destinasi.nama}” beserta foto utamanya akan
                    dihapus. Tindakan ini tidak dapat dibatalkan.
                </DialogDescription>
                <Form
                    {...destroy.form(destinasi.id)}
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
                                        : 'Ya, hapus destinasi'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
