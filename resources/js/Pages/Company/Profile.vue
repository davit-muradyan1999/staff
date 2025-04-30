<template>
  <Head :title="title" />
  <CompanyProfile
    :info="info"
    :industries="industries"
    :quantityEmployees="quantityEmployees"
    :companyTypes="companyTypes"
    :cities="cities"
    :advantageEmployees="advantageEmployees"
    :currentYear="currentYear"
    :startYear="startYear"
    :userAdvantageEmployees="userAdvantageEmployees"
    :fieldOfActivities="fieldOfActivities"
    :userFieldOfActivities="userFieldOfActivities"
    :userData="userData"
    :companyData="companyData"
  />
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import CompanyProfile from '@/Components/CompanyProfile.vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  info: Object,
  industries: Object,
  quantityEmployees: Object,
  companyTypes: Object,
  cities: Object,
  advantageEmployees: Object,
  currentYear: String,
  startYear: String,
  userAdvantageEmployees: Object,
  fieldOfActivities: Object,
  userFieldOfActivities: Object,
  userData: Object,
  companyData: Object,
})
</script>
