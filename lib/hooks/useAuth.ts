import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { authService } from '@/lib/services';

export function useAuth() {
  const router = useRouter();

  useEffect(() => {
    if (!authService.isAuthenticated()) {
      router.replace('/login');
    }
  }, [router]);
}
