import { api } from '../api';
import { ApiResponse, Client } from '../types';

export const clientsService = {
  // Liste des clients avec pagination
  async getClients(params?: {
    page?: number;
    limit?: number;
    status?: 'all' | 'prospect' | 'active' | 'inactive';
  }): Promise<ApiResponse<Client[]>> {
    const { page = 1, limit = 10, status = 'all' } = params || {};
    return await api.get<ApiResponse<Client[]>>(
      `/clients?page=${page}&limit=${limit}&status=${status}`
    );
  },

  // Récupérer un client par ID
  async getClient(id: number): Promise<Client> {
    const response = await api.get<ApiResponse<Client>>(`/clients/${id}`);
    return response.data;
  },

  // Créer un client
  async createClient(data: {
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    address?: string;
    postalCode?: string;
    city?: string;
    status?: 'prospect' | 'active' | 'inactive';
    notes?: string;
  }): Promise<Client> {
    const response = await api.post<ApiResponse<Client>>('/clients', data);
    return response.data;
  },

  // Modifier un client
  async updateClient(
    id: number,
    data: Partial<{
      firstName: string;
      lastName: string;
      email: string;
      phone: string;
      address: string;
      postalCode: string;
      city: string;
      status: 'prospect' | 'active' | 'inactive';
      notes: string;
    }>
  ): Promise<Client> {
    const response = await api.patch<ApiResponse<Client>>(`/clients/${id}`, data);
    return response.data;
  },

  // Supprimer un client
  async deleteClient(id: number): Promise<void> {
    await api.delete(`/clients/${id}`);
  },
};
