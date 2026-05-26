import { User } from '@/types/index';

export interface Groupe {
    id: number;
    name: string;
    initials: string;
    color: string
    users? : User[]
}
