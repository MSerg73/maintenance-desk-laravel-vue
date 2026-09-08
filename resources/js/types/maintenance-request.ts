export type SelectOption = {
    value: string;
    label: string;
};

export type EquipmentOption = {
    id: number;
    name: string;
    code: string;
};

export type UserOption = {
    id: number;
    name: string;
    email: string;
};

export type MaintenanceRequestFormData = {
    id?: number;
    equipment_id: number | string;
    title: string;
    description: string;
    priority: string;
    assigned_to: number | string | null;
    status?: string;
    status_label?: string;
};

export type MaintenanceRequestListItem = {
    id: number;
    title: string;
    description: string;
    priority: string;
    priority_label: string;
    status: string;
    status_label: string;
    created_at: string;
    equipment: {
        id: number;
        name: string;
        code: string;
    };
    creator: {
        id: number;
        name: string;
    };
    assignee: {
        id: number;
        name: string;
    } | null;
    next_status: SelectOption | null;
};

export type MaintenanceRequestFilters = {
    q: string;
    status: string;
    priority: string;
};

export type MaintenanceRequestPaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type MaintenanceRequestPaginator = {
    data: MaintenanceRequestListItem[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: MaintenanceRequestPaginationLink[];
    per_page: number;
    to: number | null;
    total: number;
};
