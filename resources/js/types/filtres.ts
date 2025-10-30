export interface NavigationChild {
    id: number;
    name: string;
    type: string;
    departements?: Array<{
        id: number;
        name: string;
        initials: string;
    }>;
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
