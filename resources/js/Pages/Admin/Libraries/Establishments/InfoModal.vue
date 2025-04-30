<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addEstablishmentModal"
      tabindex="-1"
      aria-labelledby="addEstablishmentModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl modal-xl-size">
        <div class="modal-content">
          <div class="modal-header text-center">
            <h4 class="modal-title">{{ !data?.id ? $t("general.addEstablishment") : $t("general.editEstablishment") }}</h4>
            <button
              type="button"
              class="btn-close d-inline-block"
              @click="closeModal"
            ></button>
          </div>
          <div class="modal-body">
            <div
              v-if="errorMsg"
              class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
              role="alert"
            >
              {{ errorMsg }}
            </div>

            <div class="mb-3 row">
              <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.company') }}</label>
              <div class="col-sm-10">
                <Multiselect
                  v-model="form.company_id"
                  valueProp="id"
                  :options="companies"
                  :searchable="true"
                  label="company_name"
                  track-by="company_name"
                  :class="{ 'is-invalid': v$.company_id.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                />
                <div class="invalid-feedback">
                  {{ v$?.company_id?.$errors?.[0]?.$message || v$?.company_id?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.positions') }}</label>
              <div class="col-sm-10">
                <Multiselect
                  v-model="form.positions"
                  mode="tags"
                  valueProp="id"
                  :options="positions"
                  :searchable="true"
                  label="name_p"
                  track-by="name_p"
                  :disabled="type == 'show' ? true : false"
                />
                <div class="invalid-feedback">
                  {{ v$?.positions?.$errors?.[0]?.$message || v$?.positions?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.title') }}</label>
              <div class="col-sm-10">
                <input
                  type="text"
                  class="form-control"
                  id="inputName"
                  v-model="form.name['ru']"
                  :class="{ 'is-invalid': v$?.name?.['ru']?.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <div class="invalid-feedback">
                  {{ v$?.name?.['ru']?.$errors?.[0]?.$message || v$?.name?.['ru']?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>
            <div class="mb-3 row">
              <label for="inputResponsibility" class="col-sm-2 col-form-label">{{ $t('general.responsibility') }}</label>
              <div class="col-sm-10">
                <input
                  type="text"
                  class="form-control"
                  id="inputResponsibility"
                  v-model="form.obligation['ru']"
                  :class="{ 'is-invalid': v$?.obligation?.['ru']?.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <div class="invalid-feedback">
                  {{ v$?.obligation?.['ru']?.$errors?.[0]?.$message || v$?.obligation?.['ru']?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>
            <div class="mb-3 row">
              <label for="inputRequirement" class="col-sm-2 col-form-label">{{ $t('general.requirement') }}</label>
              <div class="col-sm-10">
                <input
                  type="text"
                  class="form-control"
                  id="inputRequirement"
                  v-model="form.requirement['ru']"
                  :class="{ 'is-invalid': v$?.requirement?.['ru']?.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <div class="invalid-feedback">
                  {{ v$?.requirement?.['ru']?.$errors?.[0]?.$message || v$?.requirement?.['ru']?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-sm-2 col-form-label">{{ $t('general.driverLicense') }}</label>
              <div class="col-sm-10">
                <div class="form-check form-check-inline" v-for="license in driverLicenses" :key="license.id">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :id="`license${license.id}`"
                    :value="license.id"
                    :true-value="[]"
                    v-model="form.driverLicenses"
                    :disabled="type == 'show' ? true : false"
                  >
                  <label class="form-check-label" :for="`license${license.id}`">{{ license?.name_d }}</label>
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label class="col-sm-2 col-form-label">{{ $t('general.meanOfTransports') }}</label>
              <div class="col-sm-10">
                <div class="form-check form-check-inline" v-for="transport in meanOfTransports" :key="transport.id">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :id="`transport${transport.id}`"
                    :value="transport.id"
                    :true-value="[]"
                    v-model="form.meanOfTransports"
                    :disabled="type == 'show' ? true : false"
                  >
                  <label class="form-check-label" :for="`transport${transport.id}`">{{ transport?.name_m }}</label>
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="absenceOfDisability" class="col-sm-2 col-form-label">{{ $t('general.absenceOfDisability') }}</label>
              <div class="col-sm-10">
                <div class="form-check form-check-inline">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="absenceOfDisability"
                    value="1"
                    :true-value="1"
                    :false-value="0"
                    v-model="form.absence_disability"
                    :disabled="type == 'show' ? true : false"
                  >
                </div>
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-sm-2 col-form-label">{{ $t('general.gender') }}</label>
              <div class="col-sm-10">
                <div class="form-check form-check-inline">
                  <input :disabled="type == 'show' ? true : false" class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="BOTH" v-model="form.gender" :checked="form.gender == 'BOTH' ? true : false">
                  <label class="form-check-label" for="inlineRadio1">{{ $t('general.both') }}</label>
                </div>
                <div class="form-check form-check-inline">
                  <input :disabled="type == 'show' ? true : false" class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="MALE" v-model="form.gender" :checked="form.gender == 'MALE' ? true : false">
                  <label class="form-check-label" for="inlineRadio2">{{ $t('general.male') }}</label>
                </div>
                <div class="form-check form-check-inline">
                  <input :disabled="type == 'show' ? true : false" class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="FEMALE" v-model="form.gender" :checked="form.gender == 'FEMALE' ? true : false">
                  <label class="form-check-label" for="inlineRadio3">{{ $t('general.female') }}</label>
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="salary" class="col-sm-2 col-form-label">{{ $t('general.img') }}</label>
              <div class="col-sm-10">
                <input
                  type="file"
                  class="form-control d-none"
                  id="img"
                  @change="updatePhotoPreview"
                  ref="img"
                  :class="{ 'is-invalid': v$?.img.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <input
                  v-if="!imgPreview && !form?.img"
                  type="text"
                  @click="openUploadFiles"
                  value="Выбрать изображение"
                  readonly
                  class="form-control cursor-pointer"
                  >
                <div class="invalid-feedback">
                  {{ v$?.img?.$errors?.[0]?.$message || v$?.img?.$errors?.[0]?.$params?.message }}
                </div>
                <template v-if="imgPreview || form?.img">
                  <div class="col-sm-10">
                    <div class="image-block">
                      <img class="choosed-image object-cover" :src="imgPreview ? imgPreview : form?.img ?  '/storage/' + form?.img : ''">
                      <template v-if="type != 'show'">
                        <button v-if="imgPreview || form?.img" type="button" class="btn-sm btn-primary mt-1 d-inline" @click="openUploadFiles">
                          {{ $t("general.change") }}
                        </button>
                        <button v-if="imgPreview || form?.img" type="button" class="btn-sm btn-danger mt-1 d-inline" @click="removePreview">
                          {{ $t("general.delete") }}
                        </button>
                      </template>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="salary" class="col-sm-2 col-form-label">{{ $t('general.salary') }} - {{ $t('general.rubTime') }}</label>
              <div class="col-sm-10">
                <input
                  type="text"
                  class="form-control"
                  id="salary"
                  v-model="form.salary"
                  :class="{ 'is-invalid': v$?.salary?.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <div class="invalid-feedback">
                  {{ v$?.salary?.$errors?.[0]?.$message || v$?.salary?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="tax" class="col-sm-2 col-form-label">{{ $t('general.spending') }}</label>
              <div class="col-sm-10">
                <input
                  type="text"
                  class="form-control"
                  id="tax"
                  v-model="form.tax"
                  :class="{ 'is-invalid': v$?.tax?.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <div class="invalid-feedback">
                  {{ v$?.tax?.$errors?.[0]?.$message || v$?.tax?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="commission" class="col-sm-2 col-form-label">{{ $t('general.income') }}</label>
              <div class="col-sm-10">
                <input
                  type="text"
                  class="form-control"
                  id="commission"
                  v-model="form.commission"
                  :class="{ 'is-invalid': v$?.commission?.$errors.length }"
                  :disabled="type == 'show' ? true : false"
                >
                <div class="invalid-feedback">
                  {{ v$?.commission?.$errors?.[0]?.$message || v$?.commission?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </div>

            <div class="mb-3 row" v-if="data?.user?.name">
              <label for="commission" class="col-sm-2 col-form-label">{{ $t('general.hasCreated') }}</label>
              <div class="col-sm-10">
                <p class="mt-2 mb-0">
                  <template v-if="data?.user?.role != 'ADMIN'">
                    <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: data?.user?.id })">
                      {{ data?.user?.name }}
                    </a>
                  </template>
                  <template v-else>
                    {{ data?.user?.name }}
                  </template>
                </p>
              </div>
            </div>

            <div class="mb-3 row" v-if="data?.admin_user?.name">
              <label for="commission" class="col-sm-2 col-form-label">{{ $t('general.lastАdminАction') }}</label>
              <div class="col-sm-10">
                <p class="mt-2 mb-0">{{ data?.admin_user?.name }} - <span :class="useEstablishmentStatusClass(data?.admin_status)">{{ useStatusText(data?.admin_status) }}</span> ({{ data?.admin_status_updated_at }})</p>
              </div>
            </div>
          </div>
          <div class="modal-footer text-center">
            <div class="col-md-12 text-center" v-if="type != 'show'">
              <button type="submit" @click="submit(null)" class="btn btn-primary me-3" v-if="form?.id">{{ $t('general.save') }}</button>
              <button type="submit" @click="submit('DRAFT')" class="btn btn-primary me-3" v-if="!form?.id">{{ $t('general.saveAsDraft') }}</button>
              <button type="submit" v-if="(form.status != 'CONFIRMED' && form.status != 'REJECTED') || form.status == 'REJECTED'" @click="submit('CONFIRMED')" class="btn btn-primary me-3">{{ $t('general.confirm')  }}</button>
              <button type="submit" v-if="(form.id && form.status != 'CONFIRMED' && form.status != 'REJECTED')" @click="submit('REJECTED')" class="btn btn-danger me-3">{{ $t('general.reject')  }}</button>
              <button type="submit" v-if="form.status == 'CONFIRMED'" @click="submit('PASSIVATED')" class="btn btn-warning me-3">{{ $t('general.makePassive')  }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-click-away="onClickAway"></div>
  </Teleport>
</template>

<script setup>
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required, Email, MinLength, Numeric } from '@/Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import { getEstablishmentsByCompanyId } from '@/Services/Misc.js'
import { useEstablishmentStatusClass } from '@/Hooks/useEstablishmentStatusClass'
import { useStatusText } from '@/Hooks/useStatusText'

const props = defineProps({
  data: Object,
  driverLicenses: Object,
  meanOfTransports: Object,
  companies: Object,
  positions: Object,
  type: String
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const driverLicenseValue = ref([])

const img = ref(null)
const imgPreview = ref(null)
const defaultImgPreview = ref(null)

const form = useForm({
  id: props?.data?.id,
  _method: props?.data?.id ? 'PUT' : 'POST',
  status: props?.data?.status,
  company_id: props?.data?.company_id,
  name: {
    'ru': props?.data?.name_e,
  },
  obligation: {
    'ru': props?.data?.obligation_e,
  },
  requirement: {
    'ru': props?.data?.requirement_e,
  },
  driverLicenses: [],
  meanOfTransports: [],
  positions: [],
  absence_disability: props?.data?.absence_disability,
  salary: props?.data?.salary,
  gender: props?.data?.gender || 'BOTH',
  tax: props?.data?.tax,
  commission: props?.data?.commission,
  img: props?.data?.img,
  imgName: props?.data?.img,
  delete_img: false,
});

for(const value in props?.data?.driver_license_lists) {
  form.driverLicenses.push(props?.data?.driver_license_lists[value].driver_license_id)
}

for(const value in props?.data?.mean_of_transport_lists) {
  form.meanOfTransports.push(props?.data?.mean_of_transport_lists[value].mean_of_transport_id)
}

for(let p in props?.data?.positions) {
  form.positions.push(props?.data?.positions[p].id)
}

const rules = {
  company_id: { Required },
  name: {
    ru: { Required },
  },
  obligation: {
    ru: { Required },
  },
  requirement: {
    ru: { Required },
  },
  salary: { Numeric },
  tax: {  },
  commission: {  },
  img: { Required },
  positions: {  }
};

const v$ = useVuelidate(rules, form);

const submit = (status) => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  if(status)
    form.status = status

  const routeUrl = props?.data?.id ? route('admin.establishments.update', form.id) : route('admin.establishments.store')

  if(props?.data?.id) {
    axios.post(routeUrl, form,
     {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }
    ).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('admin.establishments.index'))
      }).catch(error => {
        errorMsg.value = error.response.data.message
      });
  } else {
    axios.post(routeUrl, form, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('admin.establishments.index'))
      }).catch(error => {
        errorMsg.value = error.response.data.message
      });
  }
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const deleteEstablishment = () => {
  if (confirm('Подтвердить удаление?') == true) {
    axios.post(route('delete_establishment'), form).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('establishment'))
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
  }
}

const closeModal = () => {
  emit("close")
}

const updatePhotoPreview = (event) => {
  const file = img.value.files[0]

  const reader = new FileReader();
  reader.onload = (e) => {
    imgPreview.value = e.target.result;
    form.img = file;
    event.target.value = "";
  }

  reader.readAsDataURL(file);
}

const removePreview = (event) => {
  imgPreview.value = defaultImgPreview.value = form.img = img.value = null
  form.delete_img = true
}

const openUploadFiles = () => {
  document.getElementById('img').click()
}

// const changeCompany = async (value) => {
//   form.positions = []
//   const establishmentsData = await getEstablishmentsByCompanyId(value)
//   positions.value = establishmentsData
// }

// if(props?.data?.company_id) {
//   changeCompany(props?.data?.company_id)
// }
</script>
