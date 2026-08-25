import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const prefersDark = () => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const applyTheme = (appearance: Appearance) => {
    const isDark =
        appearance === 'dark' || (appearance === 'system' && prefersDark());

    document.documentElement.classList.toggle('dark', isDark);
};

const handleSystemThemeChange = () => {
    const currentAppearance =
        (localStorage.getItem('appearance') as Appearance) ?? 'system';

    applyTheme(currentAppearance);
};

export function initializeTheme() {
    const savedAppearance =
        (localStorage.getItem('appearance') as Appearance) ?? 'system';

    applyTheme(savedAppearance);

    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>(() => {
        if (typeof window === 'undefined') {
            return 'system';
        }

        return (
            (localStorage.getItem('appearance') as Appearance | null) ??
            'system'
        );
    });

    const updateAppearance = useCallback((mode: Appearance) => {
        setAppearance(mode);

        localStorage.setItem('appearance', mode);
        setCookie('appearance', mode);

        applyTheme(mode);
    }, []);

    useEffect(() => {
        const savedAppearance =
            (localStorage.getItem('appearance') as Appearance | null) ??
            'system';

        applyTheme(savedAppearance);

        const mq = mediaQuery();
        mq?.addEventListener('change', handleSystemThemeChange);

        return () => mq?.removeEventListener('change', handleSystemThemeChange);
    }, []);

    return { appearance, updateAppearance } as const;
}
