'use client';

import React from 'react';
import Link from 'next/link';

export default function PatternsPage() {
  // TODO: Fetch and display list of patterns

  return (
    <div className="container mx-auto p-4">
      <div className="flex justify-between items-center mb-4">
        <h1 className="text-2xl font-bold">Patterns</h1>
        <Link
          href="/admin/patterns/create"
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Create New Pattern
        </Link>
      </div>
      <p>List of patterns will be displayed here.</p>
      {/* TODO: Implement patterns list */}
    </div>
  );
}
