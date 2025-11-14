import { api, saveToken, removeToken } from '../api';
import { LoginResponse } from '../types';

export const authService = {
  // Login
  async login(email: string, password: string): Promise<string> {
    const response = await api.post<LoginResponse>(
      '/auth/login',
      { email, password },
      false // pas besoin d'auth pour login
    );

    // Sauvegarder le token
    saveToken(response.token);

    return response.token;
  },

  // Register
  async register(data: {
    email: string;
    password: string;
    firstName: string;
    lastName: string;
    phone?: string;
  }): Promise<void> {
    await api.post('/auth/register', data, false);
  },

  // Logout
  logout(): void {
    removeToken();
  },

  // Check if user is authenticated
  isAuthenticated(): boolean {
    if (typeof window !== 'undefined') {
      return !!localStorage.getItem('token');
    }
    return false;
  },
};
