'use client';

import Link from 'next/link';
import { useAuth } from '@hooks/useAuth';
import { useState } from 'react';

export default function AdminHeader() {
  const { user, logout } = useAuth();
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  return (
    <header className="bg-gray-800 text-white shadow-md fixed w-full z-10">
      <div className="px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex">
            <div className="flex-shrink-0 flex items-center">
              <Link href="/admin" className="text-xl font-bold text-white">
                Admin Panel
              </Link>
            </div>
          </div>
          
          <div className="hidden sm:ml-6 sm:flex sm:items-center">
            <Link href="/" className="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700">
              Back to Site
            </Link>
            
            <div className="ml-3 relative">
              <div>
                <button
                  onClick={() => setIsMenuOpen(!isMenuOpen)}
                  className="max-w-xs bg-gray-800 flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white"
                >
                  <span className="sr-only">Open user menu</span>
                  <div className="h-8 w-8 rounded-full bg-gray-600 flex items-center justify-center">
                    <span className="text-white font-medium">
                      {user?.name.charAt(0).toUpperCase()}
                    </span>
                  </div>
                </button>
              </div>
              
              {isMenuOpen && (
                <div className="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <div className="px-4 py-2 text-sm text-gray-700 border-b">
                    Signed in as <span className="font-medium">{user?.email}</span>
                  </div>
                  <button
                    onClick={() => {
                      logout();
                      setIsMenuOpen(false);
                    }}
                    className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Sign out
                  </button>
                </div>
              )}
            </div>
          </div>
          
          <div className="-mr-2 flex items-center sm:hidden">
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
            >
              <span className="sr-only">Open main menu</span>
              <svg
                className={`${isMenuOpen ? 'hidden' : 'block'} h-6 w-6`}
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg
                className={`${isMenuOpen ? 'block' : 'hidden'} h-6 w-6`}
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      {/* Mobile menu */}
      <div className={`${isMenuOpen ? 'block' : 'hidden'} sm:hidden`}>
        <div className="px-2 pt-2 pb-3 space-y-1">
          <Link
            href="/"
            className="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700"
            onClick={() => setIsMenuOpen(false)}
          >
            Back to Site
          </Link>
        </div>
        
        <div className="pt-4 pb-3 border-t border-gray-700">
          <div className="flex items-center px-5">
            <div className="flex-shrink-0">
              <div className="h-10 w-10 rounded-full bg-gray-600 flex items-center justify-center">
                <span className="text-white font-medium">
                  {user?.name.charAt(0).toUpperCase()}
                </span>
              </div>
            </div>
            <div className="ml-3">
              <div className="text-base font-medium text-white">{user?.name}</div>
              <div className="text-sm font-medium text-gray-400">{user?.email}</div>
            </div>
          </div>
          <div className="mt-3 px-2 space-y-1">
            <button
              onClick={() => {
                logout();
                setIsMenuOpen(false);
              }}
              className="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700"
            >
              Sign out
            </button>
          </div>
        </div>
      </div>
    </header>
  );
}
