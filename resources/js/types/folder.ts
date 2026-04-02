export interface Folder {
    id: number;
    name: string;
    children: Array<Folder> | null;
}
