<script setup>
import { onMounted } from 'vue'
import Layout from '@/Layouts/Personal/AppLayout.vue';

defineOptions({ layout: Layout })

onMounted(() => {
  document.body.classList.add('sb-nav-fixed')
})

</script>

<template>
  <h1 class="mt-4">Dashboard</h1>
</template>
