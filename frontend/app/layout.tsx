// app/layout.tsx
import './globals.css';
import type { Metadata } from 'next';
import Header from '@components/layout/Header';
import Footer from '@components/layout/Footer';
import { AuthProvider } from '@contexts/AuthContext';

export const metadata: Metadata = {
  title: 'MyApp - Next.js + Laravel',
  description: 'A full-stack application with Next.js and Laravel',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body>
        <AuthProvider>
          <div className="flex flex-col min-h-screen">
            <Header />
            <main className="flex-grow pt-16 pb-8">{children}</main>
            <Footer />
          </div>
        </AuthProvider>
      </body>
    </html>
  );
}
