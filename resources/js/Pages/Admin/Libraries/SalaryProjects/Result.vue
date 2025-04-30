<template>
  <div class="row mt-3 mb-3">
    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.type"
        valueProp="value"
        :options="resultTypes.types"
        :searchable="true"
        label="name"
        track-by="name"
        :placeholder="`${$t('general.salaryProject')}`"
      />
    </div>
    <div class="col-md-2 mb-3">
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
    </div>
    <div class="col-md-2 mb-3">
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

  <div class="mt-3">
    <div class="table-responsive table-list">
      <template v-if="search.type == 'SALARY_GET_PAYMENT_REGISTRY_LIST' && data?.paymentOrders?.length > 0">
        <div class="card mt-3">
          <div class="card-body p-3 position-relative testimonial-group">
            <div class="row">
              <div class="col border-end text-primary">{{ $t("general.number") }}</div>
              <div class="col border-end text-primary">{{ $t("general.date") }}</div>
              <div class="col border-end text-primary">{{ $t("general.count") }}</div>
              <div class="col border-end text-primary">{{ $t("general.sum") }}</div>
              <div class="col border-end text-primary">{{ $t("general.status") }}</div>
            </div>
          </div>
        </div>
        <div class="card mt-3">
          <div class="card-body p-3 position-relative testimonial-group"  v-for="order in data.paymentOrders" :key="order.number">
            <div class="row">
              <div class="col border-end">{{ order?.number }}</div>
              <div class="col border-end">{{ moment(order?.date).format("YYYY-MM-DD HH:mm:ss") }}</div>
              <div class="col border-end">{{ order?.count }}</div>
              <div class="col border-end">{{ order?.sum }}</div>
              <div class="col border-end">{{ order?.status }}</div>
            </div>
          </div>
        </div>
      </template>
    </div>
    <!-- {{ data }} -->
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { useSalaryProjectResultTypes } from '@/Hooks/useSalaryProjectResultTypes'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { DatePicker } from 'v-calendar'
import moment from 'moment'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object,
  startedAt: String,
  finishedAt: String,
})

const urlParams = new URLSearchParams(window.location.search)

const search = ref({
  type: urlParams.get('type'),
  startedAt: urlParams.get('startedAt') ? urlParams.get('startedAt') : props.startedAt,
  finishedAt: urlParams.get('finishedAt') ? urlParams.get('finishedAt') : props.finishedAt,
})

const resultTypes = useSalaryProjectResultTypes()

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.salary_project_results'), {
    search: true,
    type: currentValue.type,
  }, {
    preserveState: true
  })
});

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
}

const masks = {
  input: 'YYYY-MM-DD',
}

</script>
