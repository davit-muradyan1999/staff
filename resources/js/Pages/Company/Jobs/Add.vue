<template>
<Head :title="title" />

<AddForm
  :title="title"
  :data="data"
  :establishments="establishments"
  :currencies="currencies"
  :branches="branches"
  :jobTypes="jobTypes"
  :companies="companies"
  :jobSpecialBrancheIDS="jobSpecialBrancheIDS"
  :jobSpecialEmployeeIDS="jobSpecialEmployeeIDS"
  :advantageEmployeeIDS="advantageEmployeeIDS"
  :selectedBrancheOptions="selectedBrancheOptions"
  :selectedEmployeeOptions="selectedEmployeeOptions"
  :advantageEmployees="advantageEmployees"
/>
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import AddForm from './Forms/AddForm.vue'

const props = defineProps({
  title: String,
  data: Object,
  establishments: Object,
  currencies: Object,
  branches: Object,
  jobTypes: Object,
  jobSpecialBrancheIDS: Array,
  jobSpecialEmployeeIDS: Array,
  advantageEmployeeIDS: Array,
  selectedBrancheOptions: Object,
  selectedEmployeeOptions: Object,
  companies: Object,
  advantageEmployees: Object,
});

console.log(props.advantageEmployees)

defineOptions({ layout: Layout })
</script>
