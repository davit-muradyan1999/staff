<template>
  <Head :title="title" />
  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.cities.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3">
          <label for="typeId" class="form-label">
            {{ $t("general.territorialDivisionType") }}
          </label>
          <Multiselect
            v-model="city.territory_type_id"
            valueProp="id"
            id="typeId"
            :options="territoryTypes"
            :searchable="true"
            label="name_t"
            track-by="name_t"
            :class="{ 'is-invalid': v$.territory_type_id.$errors.length }"
          />
          <div class="invalid-feedback">
            {{ v$?.territory_type_id?.$errors[0]?.$message }}
          </div>
        </div>

        <div class="mb-3">
          <label for="countryId" class="form-label">
            {{ $t("general.country") }}
          </label>
          <Multiselect
            v-model="city.country_id"
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
            v-model="city.district_id"
            valueProp="id"
            id="districtId"
            :options="districtLists"
            :searchable="true"
            label="name_d"
            track-by="name_d"
            :class="{ 'is-invalid': v$.district_id.$errors.length }"
            @change="changeDistrict"
          />
          <div class="invalid-feedback">
            {{ v$?.district_id?.$errors[0]?.$message }}
          </div>
        </div>

        <div class="mb-3">
          <label for="regionId" class="form-label">
            {{ $t("general.region") }}
          </label>
          <Multiselect
            v-model="city.region_id"
            valueProp="id"
            id="regionId"
            :options="regionLists"
            :searchable="true"
            label="name_r"
            track-by="name_r"
            :class="{ 'is-invalid': v$.region_id.$errors.length }"
          />
          <div class="invalid-feedback">
            {{ v$?.region_id?.$errors[0]?.$message }}
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
            v-model="city.name[lang.code]"
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
import { getDistrictByCountry, getRegionByDistrict } from '@/Services/Misc.js'
import useVuelidate from '@vuelidate/core'
import { Required, MinLength } from '@/Utils/validationMessages'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object,
  territoryTypes: Object,
  countries: Object,
  districts: Object,
  regions: Object
})

const districtLists = ref(props.districts);
const regionLists = ref(props.regions);

const city = useForm({
  id: props?.data?.id,
  territory_type_id: props?.data?.territory_type_id,
  country_id: props?.data?.region?.district?.country_id ?? '',
  district_id: props?.data?.region?.district_id ?? '',
  region_id: props?.data?.region_id ?? '',
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  territory_type_id: { Required },
  country_id: { Required },
  district_id: { Required },
  region_id: { Required },
  name: {
    ru: { Required },
  },
}

const v$ = useVuelidate(rules, city)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.cities.create')
    ? city.post(route('admin.cities.store'), city)
    : city.put(route('admin.cities.update', city.id), city)
}

const changeCountry = async (value) => {
  city.district_id = city.region_id = ''
  const data = await getDistrictByCountry(value)
  districtLists.value = data
}

const changeDistrict = async (value) => {
  city.region_id = ''
  const data = await getRegionByDistrict(value)
  regionLists.value = data
}
</script>
