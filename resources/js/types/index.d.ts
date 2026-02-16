export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    document? : {
        id : number,
        title : string,
        content : string,
        attachments? : Array<{
            id           : number,
            name         : string,
            storage_path : string,
            mimetype     : string,
            size         : number
        }>
        folder_id: number
    }
    parents?: Array<{
        id: number;
        name: string;
        departements: number[];
    }>;
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
    departements_ids: number[];
    role: string;
}
