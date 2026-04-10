import { Attachment } from '@/types/attachment';

export interface Document {
    id: number;
    name: string;
    content: string;
    color: string;
    attachments: Attachment[];
    departements: number[];
    folder_id: number
}
