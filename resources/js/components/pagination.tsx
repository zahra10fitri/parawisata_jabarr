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
            aria-label="Pagination data"
            className="grid grid-cols-2 items-center gap-3 sm:grid-cols-[1fr_auto_1fr]"
        >
            {previousUrl ? (
                <Button variant="outline" className="w-full sm:w-auto sm:justify-self-start" asChild>
                    <Link href={previousUrl}>Sebelumnya</Link>
                </Button>
            ) : (
                <Button variant="outline" className="w-full sm:w-auto sm:justify-self-start" disabled>
                    Sebelumnya
                </Button>
            )}
            <span className="text-muted-foreground col-span-2 row-start-1 text-center text-sm sm:col-span-1 sm:col-start-2">
                Halaman {currentPage} dari {lastPage}
            </span>
            {nextUrl ? (
                <Button variant="outline" className="w-full sm:w-auto sm:justify-self-end" asChild>
                    <Link href={nextUrl}>Berikutnya</Link>
                </Button>
            ) : (
                <Button variant="outline" className="w-full sm:w-auto sm:justify-self-end" disabled>
                    Berikutnya
                </Button>
            )}
        </nav>
    );
}
