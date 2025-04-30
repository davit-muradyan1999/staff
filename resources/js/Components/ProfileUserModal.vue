<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addModeratorModal"
      tabindex="-1"
      aria-labelledby="addModeratorModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl modal-xl-size">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.edit") }}</h4>
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
              <div class="row g-3">

                <div class="col-lg-12">
                  <div class="card mb-3">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.whoAreWe") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'whoAreWe'">
                            <textarea class="form-control" rows="3" v-model="data.who_are_we['ru']"></textarea>
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ data?.who_are_we?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
                            <template v-if="showBlock == 'whoAreWe'">
                              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setWhoAreWeFunc" />
                              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('whoAreWe')" />
                            </template>
                            <template v-else>
                              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('whoAreWe')" />
                            </template>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-3">
                    <div class="card-body">
                      <div class="row" style="display: none;">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.industry") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'industry'">
                            <Multiselect
                              v-model="data.industry_id"
                              valueProp="id"
                              :options="industries"
                              :searchable="true"
                              label="name_i"
                              track-by="name_i"
                            />
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ dataIDName?.industryName }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
                            <template v-if="showBlock == 'industry'">
                              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setIndustryFunc" />
                              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('industry')" />
                            </template>
                            <template v-else>
                              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('industry')" />
                            </template>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">Сфера деятельности</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'industry'">
                            <Multiselect
                              v-model="data.field_of_activities"
                              mode="tags"
                              valueProp="id"
                              :options="fieldOfActivities"
                              :searchable="true"
                              label="name_f"
                              track-by="name_f"
                            />
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0" v-for="(field, index) in dataUserFieldOfActivities" :key="field.id">
                              {{ field?.field_of_activity.name_f }}{{ index + 1 == dataUserFieldOfActivities.length ? '' : ', ' }}
                            </span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
                            <template v-if="showBlock == 'industry'">
                              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setIndustryFunc" />
                              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('industry')" />
                            </template>
                            <template v-else>
                              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('industry')" />
                            </template>
                          </div>
                        </div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.quantityEmployees") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'industry'">
                            <Multiselect
                              v-model="data.quantity_employee_id"
                              valueProp="id"
                              :options="quantityEmployees"
                              :searchable="true"
                              label="name_q"
                              track-by="name_q"
                            />
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ dataIDName?.quantityEmployeeName }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.foundationDate") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'industry'">
                            <Multiselect
                              v-model="data.foundation_date"
                              valueProp="year"
                              :options="years"
                              :searchable="true"
                              label="year"
                              track-by="year"
                            />
                          </template>
                          <template v-else>
                            <span class="text-muted">{{ dataIDName?.foundationDate }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.companyType") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'industry'">
                            <Multiselect
                              v-model="data.company_type_id"
                              valueProp="id"
                              :options="companyTypes"
                              :searchable="true"
                              label="name_cp"
                              track-by="name_cp"
                            />
                          </template>
                          <template v-else>
                            <span class="text-muted">{{ dataIDName?.companyTypeName }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.location") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'industry'">
                            <input
                              class="form-control d-inline"
                              v-model="data.location['ru']"
                            >
                          </template>
                          <template v-else>
                            <span class="text-muted">{{ data?.location?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-3">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.advantageEmployees") }}</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'advantageEmploye'">
                            <Multiselect
                              v-model="advantageEmployeeOptions"
                              mode="tags"
                              valueProp="id"
                              :options="advantageEmployees"
                              :searchable="true"
                              label="name_a"
                              track-by="name_a"
                            >

                            <template v-slot:tag="{ option, handleTagRemove, disabled }">
                              <div
                                class="multiselect-tag is-user"
                                :class="{
                                  'is-disabled': disabled
                                }"
                              >
                                <img style="margin-right: 7px;" width="18" height="18" class="mr-2" :src="`/storage/${option.icon}`">
                                {{ option.name_a }}
                                <span
                                  v-if="!disabled"
                                  class="multiselect-tag-remove"
                                  @mousedown.prevent="handleTagRemove(option, $event)"
                                >
                                  <span class="multiselect-tag-remove-icon"></span>
                                </span>
                              </div>
                            </template>
                            </Multiselect>

                          </template>
                          <template v-else>
                            <div class="text-muted mb-0" v-for="(advantage, index) in dataUserAdvantageEmployees" :key="advantage?.id">
                              <img v-if="advantage?.advantage_employee?.icon" style="margin-right: 7px;" width="18" height="18" class="mr-2" :src="`/storage/${advantage?.advantage_employee.icon}`">
                              {{ advantage?.advantage_employee?.name_a }}
                              <!-- {{ index + 1 == dataUserAdvantageEmployees.length ? '' : ', ' }} -->
                            </div>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
                            <template v-if="showBlock == 'advantageEmploye'">
                              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setAdvantageFunc" />
                              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('advantageEmploye')" />
                            </template>
                            <template v-else>
                              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('advantageEmploye')" />
                            </template>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card mb-3">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">{{ $t("general.contacts") }}</p>
                        </div>

                        <div class="col-sm-8">
                          <div class="mb-2">
                            <span style="width: 50px !important; display: inline-block;">
                              <font-awesome-icon icon="location-dot" class="fs-3" style="font-size: 20px !important; color: #0b5ed7;" />
                            </span>

                            <template v-if="showBlock == 'contact'">
                              <input
                                class="form-control d-inline"
                                v-model="data.email"
                                style="width: calc(100% - 55px);"
                              >
                            </template>
                            <template v-else>
                              <span class="d-inline text-muted">{{ data?.email }}</span>
                            </template>
                          </div>

                          <div class="mb-2">
                            <span style="width: 50px !important; display: inline-block;">
                              <font-awesome-icon icon="link" class="fs-3" style="font-size: 20px !important; color: #0b5ed7;" />
                            </span>

                            <template v-if="showBlock == 'contact'">
                              <input
                                class="form-control d-inline"
                                v-model="data.company_site_url"
                                style="width: calc(100% - 55px);"
                              >
                            </template>
                            <template v-else>
                              <span class="d-inline text-muted">{{ data?.company_site_url }}</span>
                            </template>
                          </div>
                          <div style="height: 250px; width: 100%; margin-top: 10px">
                            <l-map ref="map" :zoom="mapConfig.zoom" :center="data.lat && data.lng ? [data.lat, data.lng] : mapConfig.center" @dblclick="addMarker" :minZoom="3" :maxZoom="18" :zoomAnimation="true">
                              <l-tile-layer
                                v-for="layer in mapConfig.layers"
                                :key="layer.name"
                                :url="layer.url"
                                :name="layer.name"
                                :visible="layer.visible"
                                :attribution="layer.attribution"
                                layer-type="base"
                              ></l-tile-layer>
                              <l-control-layers />
                              <l-marker v-if="data.lat && data.lng" @dragend="updateMarker" :lat-lng="[data.lat, data.lng]" :draggable="showBlock == 'contact' ? true : false"></l-marker>
                            </l-map>
                          </div>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
                            <template v-if="showBlock == 'contact'">
                              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setContactFunc" />
                              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('contact')" />
                            </template>
                            <template v-else>
                              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('contact')" />
                            </template>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </form>
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
import { Required, Email, MinLength } from '@/Utils/validationMessages'
import { useUserStatuses } from '@/Hooks/useUserStatuses'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import { useLeafletConfig } from '@/Hooks/useLeafletConfig'
import {
  setWhoAreWe,
  setIndustry,
  setContact,
  setAdvantage
} from '@/Services/CompanyProfileInfo.js'

const props = defineProps({
  user: Object,
  industries: Object,
  quantityEmployees: Object,
  companyTypes: Object,
  cities: Object,
  advantageEmployees: Object,
  fieldOfActivities: Object,
  currentYear: String,
  startYear: String,
  redirectURL: String,
  errorMsg: String,
});

const emit = defineEmits(['close'])

const dataIDName = ref({
  industryName: '',
  quantityEmployeeName: '',
  foundationDate: '',
  companyTypeName: '',
  cityName: '',
  contactCityName: '',
  location: '',
})

const info = ref({});

const data = ref({
  user_id: props.user?.id,
  who_are_we: {
    'ru': ''
  },
  location: {
    'ru': ''
  },
})

const dataUserFieldOfActivities = ref(null)
// for(let p in props?.userFieldOfActivities) {
//   data.value.field_of_activities.push(props?.userFieldOfActivities[p].field_of_activity?.id)
// }

const dataUserAdvantageEmployees = ref(null)
const advantageEmployeeOptions = ref([])
for(let p in props?.userAdvantageEmployees) {
  advantageEmployeeOptions.value.push(props?.userAdvantageEmployees[p].advantage_employee?.id)
}

const years = ref([]);
for(let i = props.startYear; i <= props.currentYear; i++) {
  years.value.push({
    'year': i
  })
}

const mapConfig = useLeafletConfig()

const showBlock = ref('')
const zoom = ref(8)
const map = ref(null)

const openBlock = (type) => {
  if(showBlock.value == type) {
    showBlock.value = ''
  } else {
    showBlock.value = type
  }
}

const addMarker = (event) => {
  if(showBlock.value == 'contact' && event.latlng) {
    data.value.lat = event.latlng.lat
    data.value.lng = event.latlng.lng
  }
}

const updateMarker = (event) => {
  if(event.target?._latlng) {
    data.value.lat = event.target?._latlng?.lat
    data.value.lng = event.target?._latlng?.lng
  }
}

const setWhoAreWeFunc = async () => {
  const info = await setWhoAreWe(data.value, '/admin/users/company/edit/set_who_are_we');
  if(info?.data) {
    data.value.who_are_we['ru'] = info?.data?.who_are_we?.ru
  }
  showBlock.value = ''
}

const setIndustryFunc = async () => {
  const row = await setIndustry(data.value, '/admin/users/company/edit/set_industry')
  setNames(row)
}

const setContactFunc = async () => {
  const row = await setContact(data.value, '/admin/users/company/edit/set_contact')
  setNames(row)
}

const setAdvantageFunc = async () => {
  const row = await setAdvantage(advantageEmployeeOptions.value, '/admin/users/company/edit/set_advantage', data.value.user_id)
  dataUserAdvantageEmployees.value = row.userAdvantageEmployees
  showBlock.value = ''
}

const setNames = (row) => {
  if(row?.data) {
    data.value.industry_id = row?.data?.industry_id
    dataIDName.value.industryName = row?.data?.industry?.name_i

    data.value.quantity_employee_id = row?.data?.quantity_employee_id
    dataIDName.value.quantityEmployeeName = row?.data?.quantity_employee?.name_q

    data.value.foundation_date = row?.data?.foundation_date
    dataIDName.value.foundationDate = row?.data?.foundation_date

    data.value.company_type_id = row?.data?.company_type_id
    dataIDName.value.companyTypeName = row?.data?.company_type?.name_c

    data.value.city_id = row?.data?.city_id
    dataIDName.value.cityName = row?.data?.city?.name_c

    data.value.email = row?.data?.email
    data.value.company_site_url = row?.data?.company_site_url

    data.value.lat = row?.data?.lat
    data.value.lng = row?.data?.lng

    data.value.location['ru'] = row?.data?.location?.ru
    dataIDName.value.location = row?.data?.location?.ru

    dataUserFieldOfActivities.value = row?.userFieldOfActivities
  }
  showBlock.value = ''
}
const closeModal = () => {
  emit("close")
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getInfo = () => {
  axios.get(route('admin.get_company_info', {'user_id': props.user.id})).then((response) => {
    data.value = {
      user_id: response.data.data.user_id,
      who_are_we: {
        'ru': response.data.data.who_are_we_w
      },
      location: {
        'ru': response.data.data.location_l
      },
      industry_id: response.data.data.industry_id,
      field_of_activities: [],
      quantity_employee_id: response.data.data.quantity_employee_id,
      company_type_id: response.data.data.company_type_id,
      city_id: response.data.data.city_id,
      contact_city_id: response.data.data.contact_city_id,
      email: response.data.data.email,
      company_site_url: response.data.data.company_site_url,
      phone: response.data.data.phone,
      foundation_date: response.data.data.foundation_date,
      lat: response.data.data.lat,
      lng: response.data.data.lng,
    }

    dataUserFieldOfActivities.value = response.data.userFieldOfActivities

    for(let p in response.data.userFieldOfActivities) {
      data.value.field_of_activities.push(response.data.userFieldOfActivities[p].field_of_activity?.id)
    }

    dataUserAdvantageEmployees.value = response.data.userAdvantageEmployees
    for(let p in response.data.userAdvantageEmployees) {
      advantageEmployeeOptions.value.push(response.data.userAdvantageEmployees[p].advantage_employee?.id)
    }

    dataIDName.value.industryName = response.data.data?.industry?.name_i
    dataIDName.value.quantityEmployeeName = response.data.data?.quantity_employee?.name_q
    dataIDName.value.foundationDate = response.data.data?.foundation_date
    dataIDName.value.companyTypeName = response.data.data?.company_type?.name_c
    dataIDName.value.cityName = response.data.data?.city?.name_c
    dataIDName.value.contactCityName = response.data.data?.contactCity?.name_cc
  }).catch(error => {

  });
}

getInfo()

</script>
