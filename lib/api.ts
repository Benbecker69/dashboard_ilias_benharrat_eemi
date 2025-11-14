// Configuration de base pour les appels API

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8004/api';

// Récupérer le token JWT du localStorage
const getToken = (): string | null => {
  if (typeof window !== 'undefined') {
    return localStorage.getItem('token');
  }
  return null;
};

// Sauvegarder le token dans le localStorage
export const saveToken = (token: string) => {
  if (typeof window !== 'undefined') {
    localStorage.setItem('token', token);
  }
};

// Supprimer le token du localStorage
export const removeToken = () => {
  if (typeof window !== 'undefined') {
    localStorage.removeItem('token');
  }
};

// Interface pour les options de fetch
interface FetchOptions extends RequestInit {
  requiresAuth?: boolean;
}

// Fonction générique pour faire des appels API
async function apiFetch<T>(
  endpoint: string,
  options: FetchOptions = {}
): Promise<T> {
  const { requiresAuth = true, headers = {}, ...restOptions } = options;

  const config: RequestInit = {
    ...restOptions,
    headers: {
      'Content-Type': 'application/json',
      ...headers,
    },
  };

  // Ajouter le token si nécessaire
  if (requiresAuth) {
    const token = getToken();
    if (token) {
      (config.headers as Record<string, string>).Authorization = `Bearer ${token}`;
    }
  }

  const url = `${API_URL}${endpoint}`;

  try {
    const response = await fetch(url, config);

    // Si 401, le token est probablement expiré
    if (response.status === 401) {
      removeToken();
      if (typeof window !== 'undefined') {
        window.location.href = '/';
      }
      throw new Error('Session expirée. Veuillez vous reconnecter.');
    }

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}

// Méthodes HTTP simplifiées
export const api = {
  get: <T>(endpoint: string, requiresAuth = true) =>
    apiFetch<T>(endpoint, { method: 'GET', requiresAuth }),

  post: <T>(endpoint: string, data?: unknown, requiresAuth = true) =>
    apiFetch<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
      requiresAuth,
    }),

  patch: <T>(endpoint: string, data?: unknown, requiresAuth = true) =>
    apiFetch<T>(endpoint, {
      method: 'PATCH',
      body: data ? JSON.stringify(data) : undefined,
      requiresAuth,
    }),

  delete: <T>(endpoint: string, requiresAuth = true) =>
    apiFetch<T>(endpoint, { method: 'DELETE', requiresAuth }),
};
