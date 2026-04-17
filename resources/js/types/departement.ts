import { User } from '@/types/index';

export interface Departement {
    id: number;
    name: string;
    initials: string;
    color: string
    users? : User[]
}
