import { api } from '../api';
import { ApiResponse, Appointment } from '../types';

export const appointmentsService = {
  // Liste des rendez-vous avec filtres
  async getAppointments(params?: {
    page?: number;
    limit?: number;
    status?: string;
    type?: string;
  }): Promise<ApiResponse<Appointment[]>> {
    const { page = 1, limit = 10, status = '', type = '' } = params || {};
    const query = new URLSearchParams();
    query.append('page', page.toString());
    query.append('limit', limit.toString());
    if (status) query.append('status', status);
    if (type) query.append('type', type);

    return await api.get<ApiResponse<Appointment[]>>(`/appointments?${query.toString()}`);
  },

  // Rendez-vous du jour
  async getTodayAppointments(): Promise<Appointment[]> {
    const response = await api.get<ApiResponse<Appointment[]>>('/appointments/today');
    return response.data;
  },

  // Récupérer un rendez-vous par ID
  async getAppointment(id: number): Promise<Appointment> {
    const response = await api.get<ApiResponse<Appointment>>(`/appointments/${id}`);
    return response.data;
  },

  // Créer un rendez-vous
  async createAppointment(data: {
    clientId: number;
    userId: number;
    appointmentDate: string;
    type: string;
    status?: string;
    address: string;
    notes?: string;
  }): Promise<Appointment> {
    const response = await api.post<ApiResponse<Appointment>>('/appointments', data);
    return response.data;
  },

  // Modifier un rendez-vous
  async updateAppointment(
    id: number,
    data: Partial<{
      appointmentDate: string;
      type: string;
      status: string;
      address: string;
      notes: string;
    }>
  ): Promise<Appointment> {
    const response = await api.patch<ApiResponse<Appointment>>(`/appointments/${id}`, data);
    return response.data;
  },

  // Supprimer un rendez-vous
  async deleteAppointment(id: number): Promise<void> {
    await api.delete(`/appointments/${id}`);
  },
};
