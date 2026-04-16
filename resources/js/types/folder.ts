export interface Folder {
    id: number,
    name: string,
    is_archived: boolean, // true si l'élément est archivé, false sinon
    departements: number[],
    children: Array<Folder>,
    type: string,
    color: string
}
