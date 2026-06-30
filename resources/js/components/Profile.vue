<template>
  <div class="flex items-center justify-center min-h-screen bg-slate-950">
    <div class="text-center space-y-4">
      <h1 class="text-white font-extrabold text-xl tracking-wider">Profile</h1>
      <p class="text-slate-400 text-sm">Redirecting to profile page…</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
onMounted(() => { window.location.href = '/profile'; });
</script>
