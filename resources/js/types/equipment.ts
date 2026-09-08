export type EquipmentStatusOption = {
    value: string;
    label: string;
};

export type EquipmentFormData = {
    id?: number;
    name: string;
    code: string;
    location: string | null;
    status: string;
};

export type EquipmentListItem = EquipmentFormData & {
    id: number;
    status_label: string;
    maintenance_requests_count: number;
};

export type EquipmentFilters = {
    q: string;
    status: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type EquipmentPaginator = {
    data: EquipmentListItem[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    per_page: number;
    to: number | null;
    total: number;
};
