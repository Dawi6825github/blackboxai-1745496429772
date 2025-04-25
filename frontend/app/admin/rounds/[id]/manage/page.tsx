'use client';

import React from 'react';
import { useRouter } from 'next/navigation';

interface ManageRoundPageProps {
  params: { id: string };
}

export default function ManageRoundPage({ params }: ManageRoundPageProps) {
  const router = useRouter();
  const { id } = params;

  // TODO: Implement round management features

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-2xl font-bold mb-4">Manage Round - {id}</h1>
      <p>This page allows managing the round with ID: {id}</p>
      {/* TODO: Add management UI */}
      <button
        className="mt-4 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
        onClick={() => router.back()}
      >
        Back
      </button>
    </div>
  );
}
