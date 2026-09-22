import type { HTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export default function InputError({
    message,
    className = '',
    ...props
}: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <p
            {...props}
            className={cn('mt-1 text-sm leading-5 text-red-600 dark:text-red-400', className)}
        >
            {message}
        </p>
    ) : null;
}
