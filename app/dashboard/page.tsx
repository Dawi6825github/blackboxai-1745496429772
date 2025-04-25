'use client';

import { useEffect, useState } from 'react';
import { api } from '@lib/api';
import { useAuth } from '@hooks/useAuth';

interface DashboardData {
  message: string;
  data: any;
}

export default function DashboardPage() {
  const { user } = useAuth();
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const data = await api.get('/dashboard');
        setDashboardData(data as DashboardData);
      } catch (err: any) {
        setError(err.message || 'Failed to load dashboard data');
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (loading) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="text-center">
          <p className="text-lg">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="bg-red-100 p-4 rounded-md">
          <p className="text-red-700">{error}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <div className="px-4 py-5 sm:px-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900">
            User Dashboard
          </h3>
          <p className="mt-1 max-w-2xl text-sm text-gray-500">
            Welcome back, {user?.name}!
          </p>
        </div>
        <div className="border-t border-gray-200 px-4 py-5 sm:p-6">
          <p className="text-md text-gray-700">
            {dashboardData?.message}
          </p>
          <div className="mt-4">
            <pre className="bg-gray-100 p-4 rounded-md overflow-auto">
              {JSON.stringify(dashboardData?.data, null, 2)}
            </pre>
          </div>
        </div>
      </div>
    </div>
  );
}
