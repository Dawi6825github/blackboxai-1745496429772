import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  
  // Get auth token from cookie
  const token = request.cookies.get('token')?.value;
  
  // Get user data if available
  const userDataCookie = request.cookies.get('user_data')?.value;
  let userData = null;
  
  try {
    if (userDataCookie) {
      userData = JSON.parse(userDataCookie);
    }
  } catch (e) {
    console.error('Failed to parse user data cookie');
  }
  
  // Public routes that don't require authentication
  const publicRoutes = ['/auth/login'];
  
  // Check if the route is public
  if (publicRoutes.includes(pathname)) {
    // If user is already logged in, redirect to appropriate dashboard
    if (token && userData) {
      if (userData.role === 'admin') {
        return NextResponse.redirect(new URL('/admin/dashboard', request.url));
      } else {
        return NextResponse.redirect(new URL('/user/gameboard', request.url));
      }
    }
    return NextResponse.next();
  }
  
  // Protected routes logic
  if (!token) {
    // Redirect to login if no token
    return NextResponse.redirect(new URL('/auth/login', request.url));
  }
  
  // Role-based access control
  if (pathname.startsWith('/admin') && userData?.role !== 'admin') {
    return NextResponse.redirect(new URL('/user/gameboard', request.url));
  }
  
  if (pathname.startsWith('/user') && userData?.role !== 'user' && userData?.role !== 'admin') {
    return NextResponse.redirect(new URL('/auth/login', request.url));
  }
  
  return NextResponse.next();
}

export const config = {
  matcher: [
    '/((?!api|_next/static|_next/image|favicon.ico).*)',
  ],
};
