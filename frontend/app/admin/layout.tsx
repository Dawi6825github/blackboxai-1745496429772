'use client';

import { useAuth } from '@hooks/useAuth';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import AdminHeader from '@components/layout/AdminHeader';
import AdminSidebar from '@components/layout/AdminSidebar';

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { user, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    // Redirect if user is not an admin
    if (!loading && (!user || user.role !== 'admin')) {
      router.push('/');
    }
  }, [user, loading, router]);

  // Show loading state while checking authentication
  if (loading || !user) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <p className="text-lg">Loading...</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <AdminHeader />
      <AdminSidebar />
      <main className="pt-16 pl-64">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          {children}
        </div>
      </main>
    </div>
  );
}
