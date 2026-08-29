import { Globe, MessageCircle, Repeat2, Send, ThumbsUp } from 'lucide-react';
import * as React from 'react';

interface LinkedInPreviewProps {
    authorName: string;
    headline: string | null;
    body: string;
}

const SEE_MORE_LIMIT = 210;

function initials(name: string): string {
    const letters = name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');

    return letters === '' ? '?' : letters;
}

function renderBody(text: string): React.ReactNode {
    return text.split(/(#[\p{L}0-9_]+)/gu).map((part, index) =>
        part.startsWith('#') ? (
            <span
                key={index}
                className="font-medium text-[#0a66c2] dark:text-sky-400"
            >
                {part}
            </span>
        ) : (
            <React.Fragment key={index}>{part}</React.Fragment>
        ),
    );
}

export function LinkedInPreview({
    authorName,
    headline,
    body,
}: LinkedInPreviewProps) {
    const [expanded, setExpanded] = React.useState(false);
    const isLong = body.length > SEE_MORE_LIMIT;
    const shown =
        expanded || !isLong
            ? body
            : `${body.slice(0, SEE_MORE_LIMIT).trimEnd()}…`;

    return (
        <div className="w-full max-w-md rounded-lg border bg-card text-card-foreground shadow-sm">
            <div className="flex items-center gap-3 p-3">
                <div className="flex size-12 shrink-0 items-center justify-center rounded-full bg-muted text-sm font-semibold">
                    {initials(authorName)}
                </div>
                <div className="min-w-0">
                    <div className="truncate text-sm font-semibold">
                        {authorName || 'Your name'}
                    </div>
                    {headline ? (
                        <div className="truncate text-xs text-muted-foreground">
                            {headline}
                        </div>
                    ) : null}
                    <div className="flex items-center gap-1 text-xs text-muted-foreground">
                        now · <Globe className="size-3" />
                    </div>
                </div>
            </div>

            <div className="px-3 pb-3 text-sm whitespace-pre-wrap">
                {body.trim() === '' ? (
                    <span className="text-muted-foreground">
                        Your post preview will appear here…
                    </span>
                ) : (
                    <>
                        {renderBody(shown)}
                        {isLong && !expanded ? (
                            <button
                                type="button"
                                onClick={() => setExpanded(true)}
                                className="ml-1 text-muted-foreground hover:underline"
                            >
                                see more
                            </button>
                        ) : null}
                    </>
                )}
            </div>

            <div className="flex items-center justify-around border-t px-2 py-1 text-xs font-medium text-muted-foreground">
                <span className="flex items-center gap-1.5 px-2 py-1">
                    <ThumbsUp className="size-4" /> Like
                </span>
                <span className="flex items-center gap-1.5 px-2 py-1">
                    <MessageCircle className="size-4" /> Comment
                </span>
                <span className="flex items-center gap-1.5 px-2 py-1">
                    <Repeat2 className="size-4" /> Repost
                </span>
                <span className="flex items-center gap-1.5 px-2 py-1">
                    <Send className="size-4" /> Send
                </span>
            </div>
        </div>
    );
}
