import { Attachment } from '@/types/attachment';

export interface Document {
    id: number,
    name: string,
    content: string,
    color: string,
    attachments: Attachment[],
    groupes: number[],
    folder_id: number,
    is_archived: boolean
    type: string,
    deadline?: string
}
