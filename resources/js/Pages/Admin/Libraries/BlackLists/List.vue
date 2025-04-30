<template>
  <Head :title="title" />

  <BlackListInfoModal
    v-if="openBlackListInfoPopup"
    :employee="selectedBlackListInfoEmployee"
    @close="openBlackListInfoPopup = false"
  />

  <div class="row">

    <div class="col-md-3 mb-3">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.companies"
        valueProp="id"
        :options="companies"
        :searchable="true"
        mode="tags"
        label="company_name"
        track-by="company_name"
        :placeholder="`${$t('general.company')}`"
      />
    </div>

  </div>


  <div class="mt-3">
    <div class="card mt-3" v-for="employee in employees.data" :key="employee.id">
      <div class="card-body p-4 position-relative">
        <div class="row bg-white align-items-center">
          <div class="col-md-1 border-end">
            <img width="50" height="50" class="img-responsive img-rounded" :src="employee.profile_photo_path ? '/storage/' + employee.profile_photo_path : '/images/user.png'">
          </div>
          <div class="col border-end">
            <a class="text-decoration-none" target="_blank" :href="route('profile', { id: employee?.id })">
              {{ employee?.name }}
            </a>
          </div>
          <div class="col">
            <span class="mb-0 link-primary cursor-pointer" v-for="(list, index) in employee.black_list_employee" :key="list.id" @click="openBlackListInfo(employee, list?.company)">
              {{ list?.company.name }}{{ index + 1 == employee.black_list_employee.length ? '' : ', ' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="paginate-block-end mt-3">
    <Pagination :links="employees?.links" />
  </div>

</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import BlackListInfoModal from '@/Components/BlackListInfoModal.vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  employees: Object,
  companies: Object,
});

const urlParams = new URLSearchParams(window.location.search)

const openBlackListInfoPopup = ref(false)
const selectedBlackListInfoEmployee = ref({})

let selectedCompanies = []

if([...urlParams.getAll('companies[]')].length > 0) {
  for(let id in [...urlParams.getAll('companies[]')]) {
    selectedCompanies.push(+[...urlParams.getAll('companies[]')][id])
  }
}

const search = ref({
  text: urlParams.get('text'),
  companies: selectedCompanies,
});

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.black_lists.index'), {
    search: true,
    text: currentValue.text,
    companies: currentValue.companies,
  }, {
    preserveState: true
  })
})

const openBlackListInfo = (employee, company) => {
  const data = {
    id: employee.id,
    company_id: company.id
  }
  openBlackListInfoPopup.value = true
  selectedBlackListInfoEmployee.value = data
}

</script>
