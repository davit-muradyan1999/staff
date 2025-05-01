<template>
  <Head :title="title" />

  <div class="card mb-4">
    <div class="card-body">
      <div class="row bg-white align-items-center">
        <div class="col-md-4 mb-3">
          <div class="input-group">
            <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
            <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
              <font-awesome-icon icon="magnifying-glass" />
            </button>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <Multiselect
            v-model="search.roles"
            valueProp="value"
            :options="useRoles.roles"
            :searchable="true"
            mode="tags"
            label="name"
            track-by="name"
            :placeholder="`${$t('general.roles')}`"
          />
        </div>
        <div class="col-md-4 mb-3">
          <Multiselect
            v-model="search.status"
            valueProp="value"
            :options="useStatus.status"
            :searchable="true"
            mode="tags"
            label="name"
            track-by="name"
            :placeholder="`${$t('general.login')}`"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <DatePicker v-model="search.startedAt" :model-config="pickerConfig" :masks="masks" mode="date" locale="ru">
            <template v-slot="{ inputValue, inputEvents }">
              <input
                class="form-control check-list-dt"
                :value="inputValue"
                v-on="inputEvents"
                :placeholder="`${$t('general.beginning')}`"
              />
            </template>
          </DatePicker>
          <div class="invalid-feedback" v-if="search.startedAt && search.finishedAt && search.startedAt > search.finishedAt" style="display: block;">
            {{ $t('messages.checkTheDatesAreCorrect') }}
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <DatePicker v-model="search.finishedAt" :model-config="pickerConfig" :masks="masks" mode="date" locale="ru">
            <template v-slot="{ inputValue, inputEvents }">
              <input
                class="form-control check-list-dt"
                :value="inputValue"
                v-on="inputEvents"
                :placeholder="`${$t('general.end')}`"
              />
            </template>
          </DatePicker>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="card mt-3" v-for="item in loginHistory" :key="item.id">
      <div class="card-body p-3 position-relative">
        <div class="row bg-white align-items-center">
          <div class="col-md-1 col-sm-12 border-end">{{ item.id }}</div>
          <div class="col-md-1 col-sm-12 border-end">{{ item.type }}</div>
          <div class="col-md-1 col-sm-12 border-end">{{ item.social_type ?? '-' }}</div>
          <div class="col-md-2 col-sm-12 border-end">{{ item.remote_addr }}</div>
          <div class="col-md-2 col-sm-12 border-end">{{ item.username }}</div>
          <div class="col-md-3 col-sm-12 border-end">{{ item.user.name }}</div>
          <div class="col-md-2 col-sm-12">{{ item.created_at }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import {ref, watch} from "vue";
import {useUserRoles} from "@/Hooks/useUserRoles";
import {DatePicker} from "v-calendar";
import {router} from "@inertiajs/vue3";
import {useLoginStatus} from "@/Hooks/useLoginStatus";

const useRoles = useUserRoles()
const useStatus = useLoginStatus()
const urlParams = new URLSearchParams(window.location.search)
defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  loginHistory: Object,
  startedAt: String,
  finishedAt: String,
})

let selectedRoles = []

if([...urlParams.getAll('roles[]')].length > 0) {
  for(let id in [...urlParams.getAll('roles[]')]) {
    selectedRoles.push([...urlParams.getAll('roles[]')][id])
  }
}


const search = ref({
  text: urlParams.get('text'),
  status: urlParams.getAll('status[]'),
  roles: urlParams.getAll('roles[]'),
  startedAt: urlParams.get('startedAt') || props?.startedAt,
  finishedAt: urlParams.get('finishedAt') || props?.finishedAt,
});

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.login_history'), {
    search: true,
    text: currentValue.text,
    status: currentValue.status,
    roles: currentValue.roles,
    startedAt: currentValue.startedAt,
    finishedAt: currentValue.finishedAt,
  }, {
    preserveState: true
  })
});

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
};

const masks = {
  input: 'YYYY-MM-DD',
};
</script>

