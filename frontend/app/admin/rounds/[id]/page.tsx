'use client';

import React from 'react';
import { useRouter } from 'next/navigation';

interface RoundPageProps {
  params: { id: string };
}

export default function RoundPage({ params }: RoundPageProps) {
  const router = useRouter();
  const { id } = params;

  // TODO: Fetch and display round details by id

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-2xl font-bold mb-4">Round Details - {id}</h1>
      <p>This page shows details for round ID: {id}</p>
      {/* TODO: Implement round details and editing */}
      <button
        className="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        onClick={() => router.back()}
      >
        Back
      </button>
    </div>
  );
}
