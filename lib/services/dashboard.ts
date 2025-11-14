import { api } from '../api';
import { ApiResponse, DashboardStats, PerformanceStats, Appointment, Activity } from '../types';

export const dashboardService = {
  // Récupérer les statistiques du dashboard
  async getStats(): Promise<DashboardStats> {
    const response = await api.get<ApiResponse<DashboardStats>>('/statistics/dashboard');
    return response.data;
  },

  // Récupérer les performances
  async getPerformance(): Promise<PerformanceStats> {
    const response = await api.get<ApiResponse<PerformanceStats>>('/statistics/performance');
    return response.data;
  },

  // Récupérer les rendez-vous du jour
  async getTodayAppointments(): Promise<Appointment[]> {
    const response = await api.get<ApiResponse<Appointment[]>>('/appointments/today');
    return response.data;
  },

  // Récupérer les activités récentes
  async getRecentActivities(limit = 10): Promise<Activity[]> {
    const response = await api.get<ApiResponse<Activity[]>>(`/activities?limit=${limit}`);
    return response.data;
  },
};
