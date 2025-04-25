export interface User {
    id: number;
    name: string;
    email: string;
    role: 'user' | 'admin';
    created_at: string;
    updated_at: string;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
}

export interface LoginCredentials {
    email: string;
    password: string;
}

export interface RegisterData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}

export interface DashboardStats {
    userCount: number;
    adminCount: number;
    activeUsers: number;
    dailySignUps: number;
    monthlySignUps: number;
    revenue: {
        today: number;
        thisMonth: number;
        total: number;
    };
    systemHealth: {
        uptime: string; // e.g., "99.98%"
        lastChecked: string; // ISO date string
        serverLoad: number; // e.g., 0.73
    };
    pendingApprovals: number;
    totalPosts?: number; // if content-based
    totalReports?: number; // for moderation dashboards
}
