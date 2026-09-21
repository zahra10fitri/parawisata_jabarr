import { Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store, update } from '@/routes/admin/tempat-wisata';
import { wisataRelations, wisataTextFields } from '@/types/tempat-wisata';
import type { TempatWisata, WisataOptions } from '@/types/tempat-wisata';

type WisataForm = {
    destinasi_id: string;
    nama: string;
    lokasi: string;
    harga_tiket: string;
    deskripsi_singkat: string;
    deskripsi_lengkap: string;
    jam_operasional: string;
    waktu_terbaik: string;
    tips: string;
    foto_utama: File | null;
    galeri: File[];
    hapus_galeri: number[];
    pengalaman: number[];
    aktivitas: number[];
    fasilitas: number[];
    status: boolean;
    unggulan: boolean;
    urutan: string;
};

export default function TempatWisataForm({
    tempatWisata,
    options,
}: {
    tempatWisata?: TempatWisata;
    options: WisataOptions;
}) {
    const form = useForm<WisataForm>({
        destinasi_id: tempatWisata ? String(tempatWisata.destinasi_id) : '',
        nama: tempatWisata?.nama ?? '',
        lokasi: tempatWisata?.lokasi ?? '',
        harga_tiket: tempatWisata?.harga_tiket ?? '',
        deskripsi_singkat: tempatWisata?.deskripsi_singkat ?? '',
        deskripsi_lengkap: tempatWisata?.deskripsi_lengkap ?? '',
        jam_operasional: tempatWisata?.jam_operasional ?? '',
        waktu_terbaik: tempatWisata?.waktu_terbaik ?? '',
        tips: tempatWisata?.tips ?? '',
        foto_utama: null,
        galeri: [],
        hapus_galeri: [],
        pengalaman: tempatWisata?.pengalaman.map((item) => item.id) ?? [],
        aktivitas: tempatWisata?.aktivitas.map((item) => item.id) ?? [],
        fasilitas: tempatWisata?.fasilitas.map((item) => item.id) ?? [],
        status: tempatWisata?.status ?? false,
        unggulan: tempatWisata?.unggulan ?? false,
        urutan: String(tempatWisata?.urutan ?? 0),
    });
    const [mainPreview, setMainPreview] = useState<string | null>(null);
    const [galleryPreviews, setGalleryPreviews] = useState<string[]>([]);
    useEffect(() => {
        if (!form.data.foto_utama) {
            return;
        }
        const url = URL.createObjectURL(form.data.foto_utama);
        setMainPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.foto_utama]);
    useEffect(() => {
        const urls = form.data.galeri.map((file) => URL.createObjectURL(file));
        setGalleryPreviews(urls);
        return () => urls.forEach((url) => URL.revokeObjectURL(url));
    }, [form.data.galeri]);

    const errors = form.errors as Record<string, string | undefined>;
    const displayedPhoto = form.data.foto_utama
        ? mainPreview
        : tempatWisata?.foto_utama_url;
    const galleryCount =
        (tempatWisata?.galeri.length ?? 0) -
        form.data.hapus_galeri.length +
        form.data.galeri.length;

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            ...(tempatWisata ? { _method: 'put' } : {}),
        }));
        form.post(tempatWisata ? update.url(tempatWisata.id) : store.url(), {
            forceFormData: true,
            preserveScroll: true,
        });
    }

    function toggleSelection(
        field: 'pengalaman' | 'aktivitas' | 'fasilitas' | 'hapus_galeri',
        id: number,
    ) {
        form.setData(
            field,
            form.data[field].includes(id)
                ? form.data[field].filter((value) => value !== id)
                : [...form.data[field], id],
        );
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            {options.destinasi.length === 0 && (
                <p role="alert" className="rounded-lg border p-4 text-sm">
                    Belum ada Destinasi. Tambahkan Destinasi sebelum menyimpan
                    Tempat Wisata.
                </p>
            )}
            <fieldset
                disabled={form.processing}
                className="grid min-w-0 gap-6 disabled:opacity-70"
            >
                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="destinasi_id">Destinasi *</Label>
                        <select
                            id="destinasi_id"
                            required
                            value={form.data.destinasi_id}
                            onChange={(event) =>
                                form.setData('destinasi_id', event.target.value)
                            }
                            className="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        >
                            <option value="">Pilih Destinasi</option>
                            {options.destinasi.map((option) => (
                                <option key={option.id} value={option.id}>
                                    {option.nama}
                                    {option.status === false
                                        ? ' (nonaktif)'
                                        : ''}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.destinasi_id} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="nama">Nama tempat wisata *</Label>
                        <Input
                            id="nama"
                            required
                            maxLength={255}
                            value={form.data.nama}
                            onChange={(event) =>
                                form.setData('nama', event.target.value)
                            }
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
                            maxLength={255}
                            value={form.data.lokasi}
                            onChange={(event) =>
                                form.setData('lokasi', event.target.value)
                            }
                        />
                        <InputError message={errors.lokasi} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="harga_tiket">Harga tiket (Rp)</Label>
                        <Input
                            id="harga_tiket"
                            type="number"
                            min={0}
                            max={9999999999.99}
                            step="0.01"
                            placeholder="Kosongkan jika belum tersedia"
                            value={form.data.harga_tiket}
                            onChange={(event) =>
                                form.setData('harga_tiket', event.target.value)
                            }
                        />
                        <InputError message={errors.harga_tiket} />
                    </div>
                </div>
                <div className="space-y-3">
                    <Label htmlFor="foto_utama">Foto utama</Label>
                    {displayedPhoto && (
                        <img
                            src={displayedPhoto}
                            alt="Preview foto utama"
                            className="h-52 w-full max-w-lg rounded-lg border object-cover"
                        />
                    )}
                    <Input
                        id="foto_utama"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        onChange={(event) =>
                            form.setData(
                                'foto_utama',
                                event.target.files?.[0] ?? null,
                            )
                        }
                    />
                    <p className="text-muted-foreground text-sm">
                        JPEG, PNG, atau WebP; maksimal 5 MB dan 6000 × 6000
                        piksel. Tanpa upload baru, foto saat ini dipertahankan.
                    </p>
                    <InputError message={errors.foto_utama} />
                </div>
                {wisataTextFields.map((field) => (
                    <div key={field.name} className="space-y-2">
                        <Label htmlFor={field.name}>{field.label}</Label>
                        <textarea
                            id={field.name}
                            maxLength={field.max}
                            rows={field.name === 'deskripsi_lengkap' ? 7 : 3}
                            value={form.data[field.name]}
                            onChange={(event) =>
                                form.setData(field.name, event.target.value)
                            }
                            className="border-input focus-visible:ring-ring w-full rounded-md border bg-transparent px-3 py-2 text-sm focus-visible:ring-2 focus-visible:outline-none"
                        />
                        <InputError message={errors[field.name]} />
                    </div>
                ))}
                <div className="grid gap-4 md:grid-cols-3">
                    {wisataRelations.map((relation) => (
                        <fieldset
                            key={relation.name}
                            className="min-w-0 space-y-3 rounded-lg border p-4"
                        >
                            <legend className="px-1 font-medium">
                                {relation.label}
                            </legend>
                            {options[relation.name].length === 0 && (
                                <p className="text-muted-foreground text-sm">
                                    Belum ada pilihan.
                                </p>
                            )}
                            <div className="max-h-56 space-y-2 overflow-y-auto">
                                {options[relation.name].map((option) => (
                                    <label
                                        key={option.id}
                                        className="flex items-start gap-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={form.data[
                                                relation.name
                                            ].includes(option.id)}
                                            onChange={() =>
                                                toggleSelection(
                                                    relation.name,
                                                    option.id,
                                                )
                                            }
                                            className="accent-primary mt-0.5 size-4 shrink-0"
                                        />
                                        <span>
                                            {option.nama}
                                            {option.status === false
                                                ? ' (nonaktif)'
                                                : ''}
                                        </span>
                                    </label>
                                ))}
                            </div>
                            {Object.entries(errors)
                                .filter(
                                    ([key]) =>
                                        key === relation.name ||
                                        key.startsWith(relation.name + '.'),
                                )
                                .map(([key, message]) => (
                                    <InputError key={key} message={message} />
                                ))}
                        </fieldset>
                    ))}
                </div>
                <fieldset className="min-w-0 space-y-4 rounded-lg border p-4">
                    <legend className="px-1 font-medium">
                        Galeri foto ({galleryCount}/20)
                    </legend>
                    <p className="text-muted-foreground text-sm">
                        Centang foto lama yang ingin dihapus, lalu simpan
                        perubahan. Tambahkan maksimal 10 foto per penyimpanan;
                        total galeri maksimal 20 foto.
                    </p>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        {tempatWisata?.galeri.map((photo) => (
                            <label
                                key={photo.id}
                                className="space-y-2 rounded-lg border p-2"
                            >
                                <img
                                    src={photo.url}
                                    alt={`Foto galeri ${photo.urutan + 1}`}
                                    className={`aspect-square w-full rounded object-cover ${form.data.hapus_galeri.includes(photo.id) ? 'opacity-40' : ''}`}
                                />
                                <span className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={form.data.hapus_galeri.includes(
                                            photo.id,
                                        )}
                                        onChange={() =>
                                            toggleSelection(
                                                'hapus_galeri',
                                                photo.id,
                                            )
                                        }
                                        className="accent-destructive size-4"
                                    />
                                    Hapus foto ini
                                </span>
                            </label>
                        ))}
                    </div>
                    <Label htmlFor="galeri">Tambah foto galeri</Label>
                    <Input
                        id="galeri"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        onChange={(event) =>
                            form.setData(
                                'galeri',
                                Array.from(event.target.files ?? []),
                            )
                        }
                    />
                    <p className="text-muted-foreground text-sm">
                        Setiap foto: JPEG, PNG, atau WebP, maksimal 5 MB dan
                        6000 × 6000 piksel.
                    </p>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        {galleryPreviews.map((url, i) => (
                            <figure key={url}>
                                <img
                                    src={url}
                                    alt={`Preview galeri baru ${i + 1}`}
                                    className="aspect-square w-full rounded-lg object-cover"
                                />
                                <figcaption className="mt-1 truncate text-xs">
                                    {form.data.galeri[i]?.name}
                                </figcaption>
                            </figure>
                        ))}
                    </div>
                    {Object.entries(errors)
                        .filter(
                            ([key]) =>
                                key.startsWith('galeri') ||
                                key.startsWith('hapus_galeri'),
                        )
                        .map(([key, message]) => (
                            <InputError key={key} message={message} />
                        ))}
                    {(galleryCount > 20 || form.data.galeri.length > 10) && (
                        <p role="alert" className="text-destructive text-sm">
                            Pilihan foto melebihi batas. Pilih ulang foto atau
                            tandai foto lama untuk dihapus.
                        </p>
                    )}
                </fieldset>
                <div className="grid items-start gap-6 sm:grid-cols-3">
                    <div>
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={form.data.status}
                                onChange={(event) =>
                                    form.setData('status', event.target.checked)
                                }
                                className="accent-primary size-4"
                            />
                            Aktif
                        </label>
                        <InputError message={errors.status} />
                    </div>
                    <div>
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={form.data.unggulan}
                                onChange={(event) =>
                                    form.setData(
                                        'unggulan',
                                        event.target.checked,
                                    )
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
                            type="number"
                            min={0}
                            max={4294967295}
                            step={1}
                            required
                            value={form.data.urutan}
                            onChange={(event) =>
                                form.setData('urutan', event.target.value)
                            }
                        />
                        <InputError message={errors.urutan} />
                    </div>
                </div>
            </fieldset>
            {form.progress && (
                <div aria-live="polite">
                    <progress
                        className="w-full"
                        value={form.progress.percentage}
                        max={100}
                    />
                    Mengunggah {form.progress.percentage}%
                </div>
            )}
            <div className="flex flex-wrap gap-3">
                <Button
                    type="submit"
                    disabled={
                        form.processing ||
                        options.destinasi.length === 0 ||
                        galleryCount > 20 ||
                        form.data.galeri.length > 10
                    }
                >
                    {form.processing ? 'Menyimpan...' : 'Simpan tempat wisata'}
                </Button>
                <Button variant="outline" asChild>
                    <Link href={index()}>Kembali ke daftar</Link>
                </Button>
            </div>
        </form>
    );
}
