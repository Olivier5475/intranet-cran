export interface NavigationChild {
    id: number;
    name: string;
    type: string;
    mimetype?: string;
    departements?: number[];
    created_at: string;
}

// Le type pour les filtres (basé sur l'inject)
export interface FilterState {
    startDate?: string | null;
    endDate?: string | null;
    fileType?: string;
    sortBy?: string;
    selectedDepartments?: number[];
}
