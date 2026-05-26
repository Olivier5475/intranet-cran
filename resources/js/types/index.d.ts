import { Folder } from "@/types/folder";
import { Document } from "@/types/document"
import { Groupe } from "@/types/groupe";
export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    document? : Document
    parents?: Folder[];
    groupes: Groupe[];
};

export interface User {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    avatar?: string;
    email_verified_at?: string | null;
    created_at?: string;
    updated_at?: string;
    groupes: number[];
    role: string;
}
