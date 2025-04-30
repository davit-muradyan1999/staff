<template>
  <Head :title="title" />
  <CompanyBalance
    :companyBalance="companyBalance"
    :companyBalancelists="companyBalancelists"
    :establishments="establishments"
    :branches="branches"
    :startedAt="startedAt"
    :finishedAt="finishedAt"
    :company="company"
    :moderatorBalancelistSum="moderatorBalancelistSum"
  />
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import CompanyBalance from '@/Components/CompanyBalance.vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  companyBalance: Object,
  companyBalancelists: Object,
  establishments: Object,
  branches: Object,
  startedAt: String,
  finishedAt: String,
  company: Object,
  moderatorBalancelistSum: Number,
});

</script>
