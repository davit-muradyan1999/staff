<template>
  <Head :title="title" />
  <AddEstablishment
    v-if="openAddEstablishmentPopup"
    :data="selectedEstablishment"
    :driverLicenses="driverLicenses"
    :meanOfTransports="meanOfTransports"
    :positions="positions"
    :type="selectedModalType"
    @close="openAddEstablishmentPopup = false"
  />

  <div class="row align-items-center">
    <div class="col-md-4 mb-2">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>
    <div class="col-md-4 mb-2">
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

  <div class="mt-3 text-end">
    <button v-if="usePermissionM([3])" :disabled="$page.props.auth?.user?.role == 'COMPANY' && !$page.props.auth?.user?.company_name" @click="openAddEstablishmentPopup = true, selectedEstablishment = {}, selectedModalType = 'edit'" class="btn btn-success ps-3 pe-3 pt-2 pb-2" type="button">
      {{ $t('general.addEstablishment') }}
    </button>
  </div>

  <div class="mt-3">
    <div class="table-responsive table-list-medium">
      <div class="card mt-3" v-for="establishment in establishments.data" :key="establishment.id">
        <div class="card-body p-4 position-relative testimonial-group">
          <div class="row bg-white">
            <div class="col border-end">
              {{ establishment?.name_e }}
            </div>
            <div class="col border-end">
              {{ establishment?.obligation_e }}
            </div>
            <div class="col border-end">
              {{ establishment?.requirement_e }}
            </div>
            <div class="col border-end">
              {{ (((+establishment?.salary || 0) + (+establishment?.tax || 0) + (+establishment?.commission || 0)) || '') }}
            </div>
            <div class="col border-end" :class="useEstablishmentStatusClass(establishment?.status)">
              {{ useStatusText(establishment?.status) }}
            </div>
            <div class="col">
              <font-awesome-icon
                icon="eye"
                class="me-2 text-primary cursor-pointer"
                @click="editEstablishment(establishment, 'show')"
              />
              <font-awesome-icon
                v-if="establishment?.status != 'CONFIRMED' && establishment?.status != 'SENDED' && usePermissionM([3])"
                icon="pen-to-square"
                class="edit-pencil"
                @click="editEstablishment(establishment, 'edit')"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="paginate-block-end mt-3">
      <Pagination :links="establishments?.links" />
    </div>
  </div>

</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, watch } from 'vue'
import AddEstablishment from './Modals/AddEstablishment.vue'
import { router } from '@inertiajs/vue3'
import { useStatusText } from '@/Hooks/useStatusText'
import { useEstablishmentStatusClass } from '@/Hooks/useEstablishmentStatusClass'
import { useEstabilishmentStatuses } from '@/Hooks/useEstabilishmentStatuses'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const statuses = useEstabilishmentStatuses().statuses

let selectedStatuses = []



const props = defineProps({
  title: String,
  establishments: Object,
  driverLicenses: Object,
  meanOfTransports: Object,
  positions: Object
});

const openAddEstablishmentPopup = ref(false)
const selectedEstablishment = ref({})
const selectedModalType = ref(false)

const editEstablishment = (data, type) => {
  openAddEstablishmentPopup.value = true
  selectedEstablishment.value = data
  selectedModalType.value = type
}

if([...urlParams.getAll('statuses[]')].length > 0) {
  for(let id in [...urlParams.getAll('statuses[]')]) {
    selectedStatuses.push(+[...urlParams.getAll('statuses[]')][id])
  }
}

const search = ref({
  text: urlParams.get('text'),
  statuses: selectedStatuses,
});

watch(search.value, (currentValue, oldValue) => {
  router.get(route('establishment'), {
    search: true,
    text: currentValue.text,
    statuses: currentValue.statuses,
  }, {
    preserveState: true
  })

  allBranchesSelected.value = currentValue.branches.length > 0 ? false : true
});

</script>
