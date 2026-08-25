import { useCallback } from 'react';

export function useInitials() {
    return useCallback((fullName: string): string => {
        const names = fullName.trim().split(' ');

        if (names.length === 0 || names[0] === '') {
            return '';
        }

        const firstInitial = names[0]?.charAt(0) ?? '';
        const lastInitial =
            names.length > 1 ? (names[names.length - 1]?.charAt(0) ?? '') : '';

        return `${firstInitial}${lastInitial}`.toUpperCase();
    }, []);
}
