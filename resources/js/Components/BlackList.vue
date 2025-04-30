<template>
  <BlackListInfoModal
    v-if="openBlackListInfoPopup"
    :employee="selectedBlackListInfoEmployee"
    @close="openBlackListInfoPopup = false"
  />
  <SetBlackList
    v-if="openBlackListPopup"
    :job="selectedBlackListJob"
    @close="openBlackListPopup = false"
  />
  <div class="row align-items-center h-100 p-2 bg-white border rounded mb-3" style="margin-left: 0;" :class="employee.status == 'ADDED' ? 'black-list-row' : ''">
    <div class="col-md-1 mt-1">
      <div class="border-end">
        <img width="50" height="50" class="img-responsive img-rounded" :src="employee?.employee?.profile_photo_path ? '/storage/' + employee?.employee?.profile_photo_path : '/images/user.png'">
      </div>
    </div>
    <div class="col-md-3 mt-1">
      <div class="border-end">
        <p>
          <a class="text-decoration-none" target="_blank" :href="route('profile', { id: employee?.employee?.id })">
            {{ employee?.employee?.name }}
          </a>
        </p>
      </div>
    </div>
    <div class="col-md-4 mt-1">
      <div class="border-end">
        <p>{{ employee?.reason }}</p>
      </div>
    </div>
    <div class="col-md-3 mt-1">
      <div class="border-end">
        <p>{{ employee?.created_at }}</p>
      </div>
    </div>
    <div class="col-md-1 mt-1">
      <div class="">
        <font-awesome-icon v-if="usePermissionM([1])" :icon="employee.status == 'ADDED' ? 'rotate-left' : 'ban'" class="fs-4 text-danger cursor-pointer me-3" @click="openBlackList(employee)" />
        <font-awesome-icon icon="circle-info" class="list-info" @click="openBlackListInfo(employee?.employee)" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import BlackListInfoModal from '@/Components/BlackListInfoModal.vue'
import SetBlackList from '@/Components/SetBlackList.vue'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineProps({
  employee: Object,
});

const openBlackListInfoPopup = ref(false)
const selectedBlackListInfoEmployee = ref({})

const openBlackListPopup = ref(false)
const selectedBlackListJob = ref({})

const openBlackListInfo = (data) => {
  openBlackListInfoPopup.value = true
  selectedBlackListInfoEmployee.value = data
}

const openBlackList = (data) => {
  openBlackListPopup.value = true

  selectedBlackListJob.value = {
    jb_user_id: data?.employee?.id,
    bl_status: 'ADDED',
  }
}

</script>
