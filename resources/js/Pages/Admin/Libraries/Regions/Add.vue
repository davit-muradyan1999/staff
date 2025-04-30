<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.regions.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3">
          <label for="countryId" class="form-label">
            {{ $t("general.country") }}
          </label>
          <Multiselect
            v-model="region.country_id"
            valueProp="id"
            id="countryId"
            :options="countries"
            :searchable="true"
            label="name_c"
            track-by="name_c"
            :class="{ 'is-invalid': v$.country_id.$errors.length }"
            @change="changeCountry"
          />
          <div class="invalid-feedback">
            {{ v$?.country_id?.$errors[0]?.$message }}
          </div>
        </div>

        <div class="mb-3">
          <label for="districtId" class="form-label">
            {{ $t("general.federalDistrict") }}
          </label>
          <Multiselect
            v-model="region.district_id"
            valueProp="id"
            id="districtId"
            :options="districts"
            :searchable="true"
            label="name_d"
            track-by="name_d"
            :class="{ 'is-invalid': v$.district_id.$errors.length }"
          />
          <div class="invalid-feedback">
            {{ v$?.district_id?.$errors[0]?.$message }}
          </div>
        </div>

        <div class="mb-3"  v-for="(lang, index) in $page.props.languages" :key="lang.id">
          <label :for="`name-${lang.code}`" class="form-label">
            {{ $t("general.title") }}
            <template v-if="$page.props.languages.length > 1">({{ lang.name }})</template>
          </label>
          <input
            type="text"
            class="form-control"
            v-model="region.name[lang.code]"
            :id="`name-${lang.code}`"
            :class="{ 'is-invalid': v$?.name?.[lang.code]?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.name?.[lang.code]?.$errors?.[0]?.$message || v$?.name?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ $t("general.save") }}</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { getDistrictByCountry } from '@/Services/Misc.js'
import useVuelidate from '@vuelidate/core'
import { Required, MinLength } from '@/Utils/validationMessages'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object,
  countries: Object,
  districts: Object
})

const districts = ref(props.districts);

const region = useForm({
  id: props?.data?.id,
  country_id: props?.data?.district?.country_id ?? '',
  district_id: props?.data?.district_id ?? '',
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  country_id: { Required },
  district_id: { Required },
  name: {
    ru: { Required },
  },
}

const v$ = useVuelidate(rules, region)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.regions.create')
    ? region.post(route('admin.regions.store'), region)
    : region.put(route('admin.regions.update', region.id), region)
}

const changeCountry = async (value, firstLoad = false) => {
  region.district_id = ''
  const data = await getDistrictByCountry(value)
  districts.value = data
}
</script>
