<template>
  <Head :title="title" />
  <InfoModal
    v-if="openAddEstablishmentPopup"
    :data="selectedEstablishment"
    :driverLicenses="driverLicenses"
    :meanOfTransports="meanOfTransports"
    :companies="companies"
    :positions="positions"
    :type="selectedModalType"
    @close="openAddEstablishmentPopup = false"
  />

  <div class="row align-items-center mb-4">
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
    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.statuses"
        valueProp="value"
        :options="statuses"
        :searchable="true"
        mode="tags"
        label="name"
        track-by="name"
        :placeholder="`${$t('general.status')}`"
      />
    </div>
  </div>

  <div class="mt-3 text-end" v-if="usePermissionA([])">
    <button @click="openAddEstablishmentPopup = true, selectedEstablishment = {}" class="btn btn-success ps-3 pe-3 pt-2 pb-2 me-3" type="button">
      {{ $t('general.addEstablishment') }}
    </button>
  </div>

  <div class="mt-3">
    <div class="table-responsive table-list">
      <div class="card mt-3" v-for="establishment in establishments.data" :key="establishment.id" :class="{'table-success': establishment.status == 'CONFIRMED', 'table-danger': establishment.status == 'REJECTED'}">
        <div class="card-body p-4 position-relative testimonial-group">
          <div class="row bg-white">
            <div class="col border-end">{{ establishment?.name_e }}</div>
            <div class="col border-end">{{ establishment?.obligation_e }}</div>
            <div class="col border-end">{{ establishment?.requirement_e }}</div>
            <div class="col border-end">
              {{ establishment?.salary }} ({{ $t('general.salary') }} - {{ $t('general.rubTime') }})
            </div>
            <div class="col border-end">{{ establishment?.tax }} ({{ $t('general.spending') }})</div>
            <div class="col border-end">{{ establishment?.commission }} ({{ $t('general.income') }})</div>
            <div class="col border-end" :class="useEstablishmentStatusClass(establishment?.status)">{{ useStatusText(establishment?.status) }}</div>
            <div class="col">
              <font-awesome-icon
                icon="eye"
                class="me-2 text-primary cursor-pointer"
                @click="editEstablishment(establishment, 'show')"
              />
              <font-awesome-icon
                v-if="usePermissionA([])"
                icon="pen-to-square"
                class="edit-pencil"
                @click="editEstablishment(establishment, 'edit')"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="paginate-block-end mt-3">
    <Pagination :links="establishments?.links" />
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import InfoModal from './InfoModal.vue'
import { router } from '@inertiajs/vue3'
import { useStatusText } from '@/Hooks/useStatusText'
import { useEstablishmentStatusClass } from '@/Hooks/useEstablishmentStatusClass'
import { useEstabilishmentStatuses } from '@/Hooks/useEstabilishmentStatuses'
import { usePermissionA } from '@/Hooks/usePermissionA'

defineOptions({ layout: Layout })

const statuses = ref([])

for(let status in useEstabilishmentStatuses().statuses) {
  statuses.value.push(useEstabilishmentStatuses().statuses[status])
}

const urlParams = new URLSearchParams(window.location.search)

const props = defineProps({
  title: String,
  establishments: Object,
  driverLicenses: Object,
  meanOfTransports: Object,
  companies: Object,
  positions: Object
});

const openAddEstablishmentPopup = ref(false)
const selectedEstablishment = ref({})
let selectedCompanies = []
let selectedStatuses = []

const selectedModalType = ref('')

if([...urlParams.getAll('companies[]')].length > 0) {
  for(let id in [...urlParams.getAll('companies[]')]) {
    selectedCompanies.push(+[...urlParams.getAll('companies[]')][id])
  }
}

if([...urlParams.getAll('statuses[]')].length > 0) {
  for(let id in [...urlParams.getAll('statuses[]')]) {
    selectedStatuses.push(+[...urlParams.getAll('statuses[]')][id])
  }
}

const search = ref({
  text: urlParams.get('text'),
  companies: selectedCompanies,
  statuses: selectedStatuses,
});

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.establishments.index'), {
    search: true,
    text: currentValue.text,
    companies: currentValue.companies,
    statuses: currentValue.statuses,
  }, {
    preserveState: true
  })

  allBranchesSelected.value = currentValue.branches.length > 0 ? false : true
});

const editEstablishment = (data, type) => {
  openAddEstablishmentPopup.value = true
  selectedEstablishment.value = data
  selectedModalType.value = type
}

</script>
