import React from 'react';
import PatternDetailsClient from './PatternDetailsClient';

interface PatternPageProps {
  params: Promise<{ id: string }>;
}

export default async function PatternPage({ params }: PatternPageProps) {
  const { id } = await params;

  return <PatternDetailsClient id={id} />;
}
