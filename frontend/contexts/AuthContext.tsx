"use client"

import React, { createContext, useContext, useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { api } from '@lib/api';

type User = {
  id: number;
  username: string;
  role: 'admin' | 'user';
};

type AuthContextType = {
  user: User | null;
  loading: boolean;
  login: (username: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  isAdmin: () => boolean;
  isUser: () => boolean;
};

export const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    // Check if user is already logged in
    const checkAuth = async () => {
      try {
        const response = await api.get('/api/user');
        setUser(response.data);
      } catch (error) {
        setUser(null);
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, []);

  const login = async (username: string, password: string) => {
    setLoading(true);
    try {
      await api.post('/api/login', { username, password });
      const response = await api.get('/api/user');
      setUser(response.data);
      
      // Redirect based on role
      if (response.data.role === 'admin') {
        router.push('/admin/dashboard');
      } else {
        router.push('/user/gameboard');
      }
    } catch (error) {
      throw new Error('Login failed');
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    setLoading(true);
    try {
      await api.post('/api/logout');
      setUser(null);
      router.push('/auth/login');
    } catch (error) {
      console.error('Logout failed', error);
    } finally {
      setLoading(false);
    }
  };

  const isAdmin = () => user?.role === 'admin';
  const isUser = () => user?.role === 'user';

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, isAdmin, isUser }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
