import { Attachment } from '@/types/attachment';

export interface Document {
    id: number;
    title: string;
    content: string;
    color: string;
    attachments: Attachment[];
    departements: number[];
    folder_id: number
}
