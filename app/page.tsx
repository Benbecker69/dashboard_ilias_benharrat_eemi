'use client';

import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { authService } from '@/lib/services';

export default function Home() {
  const router = useRouter();

  useEffect(() => {
    // Si l'utilisateur est déjà connecté, rediriger vers dashboard
    // Sinon, rediriger vers login
    if (authService.isAuthenticated()) {
      router.push('/dashboard');
    } else {
      router.push('/login');
    }
  }, [router]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="text-center">
        <h1 className="text-2xl font-display font-bold text-gray-900">Chargement...</h1>
      </div>
    </div>
  );
}
