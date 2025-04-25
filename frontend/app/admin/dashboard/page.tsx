'use client';

import { useState, useEffect } from 'react';
import { api } from '@lib/api';
import { DashboardStats } from '../../../types/index';

export default function AdminDashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setLoading(true);
        const data = await api.get<DashboardStats>('/admin/stats');
        // Validate and set default values for missing fields
        const safeData: DashboardStats = {
          userCount: data.userCount ?? 0,
          adminCount: data.adminCount ?? 0,
          activeUsers: data.activeUsers ?? 0,
          dailySignUps: data.dailySignUps ?? 0,
          monthlySignUps: data.monthlySignUps ?? 0,
          revenue: {
            today: data.revenue?.today ?? 0,
            thisMonth: data.revenue?.thisMonth ?? 0,
            total: data.revenue?.total ?? 0,
          },
          systemHealth: {
            uptime: data.systemHealth?.uptime ?? 'N/A',
            lastChecked: data.systemHealth?.lastChecked ?? '',
            serverLoad: data.systemHealth?.serverLoad ?? 0,
          },
          pendingApprovals: data.pendingApprovals ?? 0,
          totalPosts: data.totalPosts ?? 0,
          totalReports: data.totalReports ?? 0,
        };
        setStats(safeData);
        setError('');
      } catch (err: any) {
        setError(err.message || 'Failed to load dashboard stats');
        setStats(null);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  if (loading) {
    return (
      <div className="text-center">
        <p className="text-lg">Loading dashboard stats...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-100 text-red-700 p-4 rounded-lg">
        <p>{error}</p>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Admin Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Total Users</h2>
          <p className="mt-2 text-3xl font-bold text-blue-600">{stats?.userCount}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Total Admins</h2>
          <p className="mt-2 text-3xl font-bold text-green-600">{stats?.adminCount}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Active Users</h2>
          <p className="mt-2 text-3xl font-bold text-purple-600">{stats?.activeUsers}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Daily Sign Ups</h2>
          <p className="mt-2 text-3xl font-bold text-indigo-600">{stats?.dailySignUps}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Monthly Sign Ups</h2>
          <p className="mt-2 text-3xl font-bold text-pink-600">{stats?.monthlySignUps}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Revenue Today</h2>
          <p className="mt-2 text-3xl font-bold text-yellow-600">${stats?.revenue.today.toFixed(2)}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Revenue This Month</h2>
          <p className="mt-2 text-3xl font-bold text-yellow-500">${stats?.revenue.thisMonth.toFixed(2)}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Total Revenue</h2>
          <p className="mt-2 text-3xl font-bold text-yellow-400">${stats?.revenue.total.toFixed(2)}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Pending Approvals</h2>
          <p className="mt-2 text-3xl font-bold text-red-600">{stats?.pendingApprovals}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">System Uptime</h2>
          <p className="mt-2 text-3xl font-bold text-gray-700">{stats?.systemHealth.uptime}</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-medium text-gray-900">Server Load</h2>
          <p className="mt-2 text-3xl font-bold text-gray-700">{stats?.systemHealth.serverLoad}</p>
        </div>
      </div>
    </div>
  );
}
