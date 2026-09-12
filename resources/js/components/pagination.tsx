import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type PaginationProps = {
    currentPage: number;
    lastPage: number;
    previousUrl: string | null;
    nextUrl: string | null;
};

export default function Pagination({
    currentPage,
    lastPage,
    previousUrl,
    nextUrl,
}: PaginationProps) {
    return (
        <nav
            aria-label="Pagination destinasi"
            className="flex flex-wrap items-center justify-between gap-3"
        >
            {previousUrl ? (
                <Button variant="outline" asChild>
                    <Link href={previousUrl}>Sebelumnya</Link>
                </Button>
            ) : (
                <Button variant="outline" disabled>
                    Sebelumnya
                </Button>
            )}
            <span className="text-muted-foreground text-sm">
                Halaman {currentPage} dari {lastPage}
            </span>
            {nextUrl ? (
                <Button variant="outline" asChild>
                    <Link href={nextUrl}>Berikutnya</Link>
                </Button>
            ) : (
                <Button variant="outline" disabled>
                    Berikutnya
                </Button>
            )}
        </nav>
    );
}
