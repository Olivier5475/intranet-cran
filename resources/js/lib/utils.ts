import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function  decodeEntities(str : string) {
    const txt = document.createElement("textarea");
    txt.innerHTML = str;
    return txt.value;
}
