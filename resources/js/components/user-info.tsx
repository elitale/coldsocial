import { useInitials } from '@/hooks/use-initials';
import type { User } from '@/types';

export default function UserInfo({
    user,
    showEmail = false,
}: {
    user: User;
    showEmail?: boolean;
}) {
    const getInitials = useInitials();

    return (
        <>
            <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium text-foreground">
                {getInitials(user.name)}
            </span>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                {showEmail && (
                    <span className="truncate text-xs text-muted-foreground">
                        {user.email}
                    </span>
                )}
            </div>
        </>
    );
}
