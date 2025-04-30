<template>
  <Head :title="title" />
  <CompanyBalance
    :companyBalance="companyBalance"
    :companyBalancelists="companyBalancelists"
    :establishments="establishments"
    :branches="branches"
    :startedAt="startedAt"
    :finishedAt="finishedAt"
    :moderatorBalancelistSum="moderatorBalancelistSum"
  />
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
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
  moderatorBalancelistSum: Number,
});

</script>
