<template>
  <Head :title="title" /><Head :title="title" />
  <JobInfo :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :showMore="true"  />
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import JobInfo from '@/Components/JobInfo.vue'
import { ref, watch } from 'vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  job: Object,
})

</script>
