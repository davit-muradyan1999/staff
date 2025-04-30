<script setup>
import { onMounted } from 'vue'
import Layout from '@/Layouts/Admin/AppLayout.vue';

defineOptions({ layout: Layout })

onMounted(() => {
  document.body.classList.add('sb-nav-fixed')
})

</script>

<template>
  <h1 class="mt-4">Admin Dashboard</h1>
</template>
