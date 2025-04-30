<template>
  <Head :title="title" />
  <WorkerProfile
    :positions="positions"
    :userPositions="userPositions"
    :fieldOfActivities="fieldOfActivities"
    :userFieldOfActivities="userFieldOfActivities"
    :educations="educations"
    :userEducations="userEducations"
    :professionalSkills="professionalSkills"
    :userProfessionalSkills="userProfessionalSkills"
    :personalSkills="personalSkills"
    :userPersonalSkills="userPersonalSkills"
    :languageSkills="languageSkills"
    :userLanguageSkills="userLanguageSkills"
    :driverLicenses="driverLicenses"
    :userDriverLicenses="userDriverLicenses"
    :hobbies="hobbies"
    :userHobbies="userHobbies"
    :userExperiences="userExperiences"
    :userData="userData"
  />
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import WorkerProfile from '@/Components/WorkerProfile.vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  positions: Object,
  userPositions: Object,
  fieldOfActivities: Object,
  userFieldOfActivities: Object,
  educations: Object,
  userEducations: Object,
  professionalSkills: Object,
  userProfessionalSkills: Object,
  personalSkills: Object,
  userPersonalSkills: Object,
  languageSkills: Object,
  userLanguageSkills: Object,
  driverLicenses: Object,
  userDriverLicenses: Object,
  hobbies: Object,
  userHobbies: Object,
  userExperiences: Object,
  userData: Object,
})

</script>
