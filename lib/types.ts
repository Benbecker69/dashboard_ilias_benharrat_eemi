// Types pour les données de l'API

export interface Client {
  id: number;
  firstName: string;
  lastName: string;
  fullName: string;
  email: string;
  phone: string;
  address?: string;
  postalCode?: string;
  city?: string;
  status: 'prospect' | 'active' | 'inactive';
  notes?: string;
  createdAt: string;
  updatedAt: string;
}

export interface Appointment {
  id: number;
  appointmentDate: string;
  time: string;
  type: 'Installation' | 'Visite technique' | 'Signature' | 'SAV' | 'Autre';
  status: 'scheduled' | 'confirmed' | 'urgent' | 'done' | 'cancelled';
  address: string;
  notes?: string;
  client: {
    id: number;
    fullName: string;
    phone: string;
  };
  user?: {
    id: number;
    fullName: string;
  };
}

export interface Quote {
  id: number;
  reference: string;
  amount: number;
  powerKwc: number;
  status: 'draft' | 'sent' | 'signed' | 'rejected';
  validUntil?: string;
  signedAt?: string;
  description?: string;
  client: {
    id: number;
    fullName: string;
  };
  user: {
    id: number;
    fullName: string;
  };
  createdAt: string;
}

export interface Activity {
  id: number;
  type: 'rdv' | 'devis' | 'client' | 'study' | 'other';
  title: string;
  description?: string;
  status: 'new' | 'in_progress' | 'done';
  time: string;
  client?: {
    id: number;
    fullName: string;
  };
  user?: {
    id: number;
    fullName: string;
  };
  createdAt: string;
}

export interface SolarStudy {
  id: number;
  projectName: string;
  roofSurface: number;
  estimatedPower: number;
  annualProduction?: number;
  estimatedCost?: number;
  annualSavings?: number;
  paybackPeriod?: number;
  status: 'draft' | 'completed' | 'sent';
  client: {
    id: number;
    fullName: string;
  };
  createdAt: string;
}

export interface DashboardStats {
  appointmentsThisMonth: {
    value: number;
    change: string;
    changeType: 'positive' | 'negative';
  };
  activeClients: {
    value: number;
    change: string;
    changeType: 'positive' | 'negative';
  };
  quotesInProgress: {
    value: number;
    change: string;
    changeType: 'positive' | 'negative';
  };
  revenue: {
    value: string;
    change: string;
    changeType: 'positive' | 'negative';
  };
}

export interface PerformanceStats {
  visitsCompleted: {
    value: number;
    total: number;
    percentage: number;
  };
  quotesSigned: {
    value: number;
    total: number;
    percentage: number;
  };
  conversionRate: number;
  estimatedCommission: number;
}

export interface LoginResponse {
  token: string;
}

export interface ApiResponse<T> {
  status: number;
  data: T;
  message?: string;
  pagination?: {
    page: number;
    limit: number;
    total: number;
    pages: number;
  };
}
