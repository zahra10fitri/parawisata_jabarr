import { Form, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store, update } from '@/routes/admin/destinasi';
import { destinasiTextFields } from '@/types/destinasi';
import type { Destinasi } from '@/types/destinasi';

export default function DestinasiForm({
    destinasi,
}: {
    destinasi?: Destinasi;
}) {
    const [photo, setPhoto] = useState<File | null>(null);
    const [preview, setPreview] = useState<string | null>(null);

    useEffect(() => {
        if (!photo) {
            return;
        }
        const url = URL.createObjectURL(photo);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [photo]);

    const displayedPhoto = photo ? preview : destinasi?.foto_utama_url;

    return (
        <Form
            action={destinasi ? update.url(destinasi.id) : store.url()}
            method="post"
            transform={(data) => ({
                ...data,
                status: data.status === '1',
                unggulan: data.unggulan === '1',
            })}
            className="space-y-6"
        >
            {({ processing, errors, progress }) => (
                <>
                    {destinasi && (
                        <input type="hidden" name="_method" value="put" />
                    )}
                    <fieldset
                        disabled={processing}
                        className="grid min-w-0 gap-6 disabled:opacity-70"
                    >
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="nama">Nama destinasi *</Label>
                                <Input
                                    id="nama"
                                    name="nama"
                                    defaultValue={destinasi?.nama}
                                    maxLength={255}
                                    required
                                    autoFocus
                                    aria-invalid={Boolean(errors.nama)}
                                />
                                <InputError message={errors.nama} />
                                <p className="text-muted-foreground text-sm">
                                    Slug dibuat otomatis dari nama.
                                </p>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="lokasi">Lokasi</Label>
                                <Input
                                    id="lokasi"
                                    name="lokasi"
                                    defaultValue={destinasi?.lokasi ?? ''}
                                    maxLength={255}
                                />
                                <InputError message={errors.lokasi} />
                            </div>
                        </div>
                        <div className="space-y-3">
                            <Label htmlFor="foto_utama">Foto utama</Label>
                            {displayedPhoto && (
                                <img
                                    src={displayedPhoto}
                                    alt="Preview foto utama destinasi"
                                    className="h-52 w-full max-w-lg rounded-lg border object-cover"
                                />
                            )}
                            <Input
                                id="foto_utama"
                                name="foto_utama"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={(event) =>
                                    setPhoto(event.target.files?.[0] ?? null)
                                }
                            />
                            <p className="text-muted-foreground text-sm">
                                JPEG, PNG, atau WebP. Maksimal 5 MB dan 6000 ×
                                6000 piksel. Kosongkan untuk mempertahankan foto
                                saat ini.
                            </p>
                            <InputError message={errors.foto_utama} />
                        </div>
                        {destinasiTextFields.map((field) => (
                            <div key={field.name} className="space-y-2">
                                <Label htmlFor={field.name}>
                                    {field.label}
                                </Label>
                                <textarea
                                    id={field.name}
                                    name={field.name}
                                    defaultValue={destinasi?.[field.name] ?? ''}
                                    maxLength={field.max}
                                    rows={
                                        field.name === 'deskripsi_lengkap'
                                            ? 7
                                            : 3
                                    }
                                    className="border-input focus-visible:ring-ring flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:outline-none"
                                />
                                <InputError message={errors[field.name]} />
                            </div>
                        ))}
                        <div className="grid items-start gap-6 sm:grid-cols-3">
                            <div className="space-y-2">
                                <label className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="status"
                                        value="1"
                                        defaultChecked={
                                            destinasi?.status ?? false
                                        }
                                        className="accent-primary size-4"
                                    />
                                    Aktif
                                </label>
                                <InputError message={errors.status} />
                            </div>
                            <div className="space-y-2">
                                <label className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="unggulan"
                                        value="1"
                                        defaultChecked={
                                            destinasi?.unggulan ?? false
                                        }
                                        className="accent-primary size-4"
                                    />
                                    Unggulan
                                </label>
                                <InputError message={errors.unggulan} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="urutan">Urutan *</Label>
                                <Input
                                    id="urutan"
                                    name="urutan"
                                    type="number"
                                    min={0}
                                    max={4294967295}
                                    step={1}
                                    defaultValue={destinasi?.urutan ?? 0}
                                    required
                                />
                                <InputError message={errors.urutan} />
                            </div>
                        </div>
                    </fieldset>
                    {progress && (
                        <div aria-live="polite">
                            <progress
                                className="w-full"
                                value={progress.percentage}
                                max={100}
                            />
                            Mengunggah {progress.percentage}%
                        </div>
                    )}
                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : 'Simpan destinasi'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={index()}>Kembali ke daftar</Link>
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
