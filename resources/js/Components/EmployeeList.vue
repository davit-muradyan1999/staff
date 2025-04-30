<template>
  <JobGraphic
    v-if="openGraphicPopup"
    :job="selectedGraphicJob"
    @close="openGraphicPopup = false"
  />
  <SetBlackList
    v-if="openBlackListPopup"
    :job="selectedBlackListJob"
    @close="openBlackListPopup = false"
  />
  <EmployeeHistoryList
    v-if="openEmployeeHistoryPopup"
    :job="selectedJobHistory"
    @close="openEmployeeHistoryPopup = false"
  />
  <BlackListInfoModal
    v-if="openBlackListInfoPopup"
    :employee="selectedBlackListInfoEmployee"
    @close="openBlackListInfoPopup = false"
  />
  <div class="row align-items-center h-100 p-2 bg-white border rounded mb-3" style="margin-left: 0;" :class="job.bl_status == 'ADDED' ? 'black-list-row' : ''">
    <template v-if="type != 'history'">
      <div class="col-md-1 mt-1">
        <img width="50" height="50" class="img-responsive img-rounded" :src="job.user_profile_photo_path ? '/storage/' + job.user_profile_photo_path : '/images/user.png'">
      </div>
      <div class="col-md-3 mt-1">
        <div class="border-end">
          <p>
            <a class="text-decoration-none" target="_blank" :href="route('profile', { id: job?.jb_user_id })">
              {{ job?.user_name }}
            </a>
          </p>
        </div>
      </div>
      <div class="col-md-2 mt-1">
        <div class="border-end">
          <p>
            <a class="text-decoration-none" target="_blank" :href="route('company_jobs_show_list', job.id)">
              {{ job?.establishment?.name_e }} <span class="job-id">#{{ job.id }}</span>
            </a>
          </p>
        </div>
      </div>
      <div class="col-md-2 mt-1">
        <div class="border-end">
          <p>{{ job?.branche?.title_b || job?.establishment?.company?.company_name }}</p>
        </div>
      </div>
      <div class="col-md-2 mt-1">
          <!-- <div class="text-decoration-none link-primary cursor-pointer" @click="openGraphic(job)"> -->
        <div class="border-end">
          <p>{{ $t('general.accepted') }} {{ job?.jb_created_at }}</p>
        </div>
          <!-- </div> -->
      </div>
      <div class="col-md-2 mt-1">
        <p class="text-decoration-none link-primary cursor-pointer">
          <font-awesome-icon icon="calendar-days" class="fs-4 me-3" @click="openGraphic(job)" />
          <font-awesome-icon :icon="job.bl_status == 'ADDED' ? 'rotate-left' : 'ban'" class="fs-4 text-danger me-3" @click="openBlackList(job)" />
          <font-awesome-icon v-if="job?.general_bl_id" icon="circle-info" class="list-info" @click="openBlackListInfo(job)" />
        </p>
      </div>
    </template>
    <template v-else>
      <div class="col-md-1 mt-1">
        <img width="50" height="50" class="img-responsive img-rounded" :src="job.user_profile_photo_path ? '/storage/' + job.user_profile_photo_path : '/images/user.png'">
      </div>
      <div class="col-md-7 mt-1">
        <div class="border-end">
          <p>
            <a class="text-decoration-none" target="_blank" :href="route('profile', { id: job?.jb_user_id })">
              {{ job?.user_name }}
            </a>
          </p>
        </div>
      </div>
      <div class="col-md-4 mt-1">
        <div class="border-end">
          <font-awesome-icon icon="list" class="list-info me-3" @click="openEmployeeHistoryInfo(job)" />
          <font-awesome-icon v-if="usePermissionM([3])" :icon="job.bl_status == 'ADDED' ? 'rotate-left' : 'ban'" class="fs-4 text-danger cursor-pointer me-3" @click="openBlackList(job)" />
          <font-awesome-icon v-if="job?.general_bl_id" icon="circle-info" class="list-info" @click="openBlackListInfo(job)" />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import JobGraphic from '@/Components/JobGraphic.vue'
import SetBlackList from '@/Components/SetBlackList.vue'
import EmployeeHistoryList from '@/Components/EmployeeHistoryList.vue'
import BlackListInfoModal from '@/Components/BlackListInfoModal.vue'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineProps({
  type: String,
  job: Object,
});

const openGraphicPopup = ref(false)
const selectedGraphicJob = ref({})

const openBlackListPopup = ref(false)
const selectedBlackListJob = ref({})

const openEmployeeHistoryPopup = ref(false)
const selectedJobHistory = ref({})

const openBlackListInfoPopup = ref(false)
const selectedBlackListInfoEmployee = ref({})

const openGraphic = (data) => {
  openGraphicPopup.value = true
  selectedGraphicJob.value = data
}

const openBlackList = (data) => {
  openBlackListPopup.value = true
  selectedBlackListJob.value = data
}

const openEmployeeHistoryInfo = (data) => {
  openEmployeeHistoryPopup.value = true
  selectedJobHistory.value = data
}

const openBlackListInfo = (data) => {
  openBlackListInfoPopup.value = true
  selectedBlackListInfoEmployee.value = data
}
</script>
