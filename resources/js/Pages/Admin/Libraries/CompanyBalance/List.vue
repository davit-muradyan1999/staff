<template>
  <div class="card mb-4 total-info">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">{{ $t('general.summation') }}<span class="h4 ms-1 text-danger" :class="totalCompaniesBalance >= 0 ? 'text-success' : 'text-danger'">{{ useFixedNumber(totalCompaniesBalance) }}</span>
            <span class="ms-1 text-danger">руб</span>
          </div>
        </div>
      </div>
    </div>

  <div class="row mt-3 mb-3">
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
  </div>

  <div class="mt-3">
    <div class="card mt-3 table-success" v-for="company in companiesBalance" :key="company.id">
      <div class="card-body p-4 position-relative testimonial-group">
        <div class="row bg-white">
          <div class="col border-end">
            <a :href="route('balance_info') + `?search=true&companyId=${company?.company_id}`" target="_blank">
              {{ company?.company?.company_name }}
            </a>
          </div>
          <div class="col border-end">
            <span :class="company?.balance >= 0 ? 'text-success' : 'text-danger'">{{ company?.balance }} {{ $t('general.rub') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useFixedNumber } from '@/Hooks/useFixedNumber'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  companies: Object,
  companiesBalance: Object,
  totalCompaniesBalance: Number
})

const urlParams = new URLSearchParams(window.location.search)

let selectedCompanies = []

if([...urlParams.getAll('companies[]')].length > 0) {
  for(let id in [...urlParams.getAll('companies[]')]) {
    selectedCompanies.push(+[...urlParams.getAll('companies[]')][id])
  }
}

const search = ref({
  text: urlParams.get('text'),
  companies: selectedCompanies,
})

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.companies_balance'), {
    search: true,
    text: currentValue.text,
    companies: currentValue.companies,
  }, {
    preserveState: true
  })
});

</script>
