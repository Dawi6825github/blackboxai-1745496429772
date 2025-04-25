"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

export default function RegisterPage() {
  const router = useRouter();

  useEffect(() => {
    // Redirect to login page since public registration is disabled
    router.replace("/auth/login");
  }, [router]);

  return null;
}
