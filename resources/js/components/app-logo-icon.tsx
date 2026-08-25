import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
                fill="currentColor"
                d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Zm0 13.1a6.5 6.5 0 0 1-5.2-2.6c.03-1.72 3.47-2.66 5.2-2.66s5.17.94 5.2 2.66A6.5 6.5 0 0 1 12 18.6Z"
            />
        </svg>
    );
}
