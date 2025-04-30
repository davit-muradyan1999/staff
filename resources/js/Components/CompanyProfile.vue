<template>
  <div class="row mt-2">
    <div class="col-lg-3 position-relative">
      <div class="card mb-4" v-if="$page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR'">
        <div class="card-body text-center">
          <div class="profile-img">
            <input
              ref="userProfileImg"
              id="userProfileImg"
              type="file"
              class="d-none"
              @change="updatePhotoPreview($event, 'userProfileImg')"
            >
            <img
              :src="userProfileImgPreview ? userProfileImgPreview : (userData.profile_photo_path ? '/storage/' + userData.profile_photo_path : 'https://avatars.mds.yandex.net/i?id=0393e6b5b8874243329b425b83cc44b0e64b1b89-8986614-images-thumbs&n=13')"
              alt="avatar"
              class="rounded-circle img-fluid"
              :class="{'opacity-50': userData.img_remove}"
            >

            <font-awesome-icon v-if="showBlock == 'profile'" icon="paperclip" class="fs-6 me-1 edit-img" @click="openUploadFile('userProfileImg')" />
            <font-awesome-icon v-if="showBlock == 'profile' && userData.profile_photo_path" :icon="userData.img_remove ? 'rotate' : 'trash'" class="fs-6 me-2 edit-img-trash" @click="userData.img_remove = !userData.img_remove, userProfileImgPreview = null, userData.img = null, userData.imgFile = null" />
          </div>

          <div>
            <template v-if="userProfileImgPreview">
              <span class="text-danger cursor-pointer" @click="userProfileImgPreview = null, userData.img = null, userData.imgFile = null">Удалить</span>
            </template>
            <div v-if="u$?.imgFile?.$errors?.[0]?.$message || u$?.imgFile?.$errors?.[0]?.$params?.message" class="invalid-feedback text-start m-0 d-block">
              {{ u$?.imgFile?.$errors?.[0]?.$message || u$?.imgFile?.$errors?.[0]?.$params?.message }}
            </div>
          </div>

          <div>
            <template v-if="showBlock == 'profile'">
              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="userData.name"
                  :class="{ 'is-invalid': u$?.name?.$errors.length }"
                  :placeholder="`${$t('general.firstName')}, ${$t('general.lastName')}`"
                >
                <div class="invalid-feedback text-start  m-0">
                  {{ u$?.name?.$errors?.[0]?.$message || u$?.name?.$errors?.[0]?.$params?.message }}
                </div>
              </div>

              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="userData.email"
                  :class="{ 'is-invalid': u$?.email?.$errors.length }"
                  :placeholder="`${$t('general.email')}`"
                >
                <div class="invalid-feedback text-start  m-0">
                  {{ u$?.email?.$errors?.[0]?.$message || u$?.email?.$errors?.[0]?.$params?.message }}
                </div>
              </div>

              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="userData.phone"
                  :class="{ 'is-invalid': u$?.phone?.$errors.length }"
                  :placeholder="`${$t('general.phone')}`"
                >
                <div class="invalid-feedback text-start  m-0">
                  {{ u$?.phone?.$errors?.[0]?.$message || u$?.phone?.$errors?.[0]?.$params?.message }}
                </div>
              </div>

              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="userData.password"
                  autocomplete="off"
                  type="password"
                  :class="{ 'is-invalid': u$?.password?.$errors.length }"
                  :placeholder="`${$t('general.password')}`"
                >
                <div class="invalid-feedback text-start  m-0">
                  {{ u$?.password?.$errors?.[0]?.$message || u$?.password?.$errors?.[0]?.$params?.message }}
                </div>
              </div>

              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="userData.password_confirmation"
                  type="password"
                  :class="{ 'is-invalid': u$?.password_confirmation?.$errors.length }"
                  :placeholder="`${$t('general.confirmPassword')}`"
                >
                <div class="invalid-feedback text-start m-0">
                  {{ u$?.password_confirmation?.$errors?.[0]?.$message || u$?.password_confirmation?.$errors?.[0]?.$params?.message }}
                </div>
                <div class="invalid-feedback text-start m-0 d-block" v-if="(userData.password || userData.password_confirmation) && userData.password != userData.password_confirmation">
                  Поля «Пароль» и «Подтвердит пароль» должны быть равны.
                </div>
              </div>
            </template>
            <template v-else>
              <h5 class="my-3">{{ userData.name }}</h5>
              <p class="text-muted mb-1">{{ userData.email }}</p>
              <p class="text-muted mb-1">{{ userData.phone }}</p>
            </template>
          </div>

          <div class="profile-save-icon" v-if="$page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR'" style="position: absolute; top: 10px; right: 10px;">
            <template v-if="showBlock == 'profile'">
              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setUserInfoFunc" />
              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('profile')" />
            </template>
            <template v-else>
              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('profile')" />
            </template>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body text-center">
          <div class="profile-img">
            <input
              ref="companyProfileImg"
              id="companyProfileImg"
              type="file"
              class="d-none"
              @change="updatePhotoPreview($event, 'companyProfileImg')"
            >
            <img
              :src="companyProfileImgPreview ? companyProfileImgPreview : (companyData.company_photo_path ? '/storage/' + companyData.company_photo_path : 'https://avatars.mds.yandex.net/i?id=0393e6b5b8874243329b425b83cc44b0e64b1b89-8986614-images-thumbs&n=13')"
              alt="avatar"
              class="rounded-circle img-fluid"
              :class="{'opacity-50': companyData.img_remove}"
            >
            <font-awesome-icon v-if="showBlock == 'profileCompany'" icon="paperclip" class="fs-6 me-1 edit-img" @click="openUploadFile('companyProfileImg')" />
            <font-awesome-icon v-if="showBlock == 'profileCompany' && companyData.company_photo_path" :icon="companyData.img_remove ? 'rotate' : 'trash'" class="me-2 edit-img-trash" @click="companyData.img_remove = !companyData.img_remove, companyProfileImgPreview = null, companyData.img = null, companyData.imgFile = null" />
          </div>

          <div>
            <template v-if="companyProfileImgPreview">
              <span class="text-danger cursor-pointer" @click="companyProfileImgPreview = null, companyData.img = null, companyData.imgFile = null">Удалить</span>
            </template>
            <div v-if="c$?.imgFile?.$errors?.[0]?.$message || c$?.imgFile?.$errors?.[0]?.$params?.message" class="invalid-feedback text-start m-0 d-block">
              {{ c$?.imgFile?.$errors?.[0]?.$message || c$?.imgFile?.$errors?.[0]?.$params?.message }}
            </div>
          </div>

          <div>
            <template v-if="showBlock == 'profileCompany'">
              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="companyData.company_name"
                  :class="{ 'is-invalid': c$?.company_name?.$errors.length }"
                  :placeholder="`${$t('general.companyName')}`"
                >
                <div class="invalid-feedback text-start m-0">
                  {{ c$?.company_name?.$errors?.[0]?.$message || c$?.company_name?.$errors?.[0]?.$params?.message }}
                </div>
              </div>

              <div class="col-md-12">
                <input
                  class="form-control d-inline my-1"
                  v-model="companyData.company_phone"
                  :class="{ 'is-invalid': c$?.company_phone?.$errors.length }"
                  :placeholder="`${$t('general.companyPhone')}`"
                >
                <div class="invalid-feedback text-start m-0">
                  {{ c$?.company_phone?.$errors?.[0]?.$message || c$?.company_phone?.$errors?.[0]?.$params?.message }}
                </div>
              </div>
            </template>
            <template v-else>
              <h5 class="my-3">{{ companyData.company_name || $t("general.notIndicated") }}</h5>
              <p class="text-muted mb-1">{{ companyData.company_phone || $t("general.notIndicated") }}</p>
            </template>
          </div>

          <div class="profile-save-icon" v-if="($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR') && usePermissionM([3])" style="position: absolute; top: 10px; right: 10px;">
            <template v-if="showBlock == 'profileCompany'">
              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setCompanyInfoFunc" />
              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('profileCompany')" />
            </template>
            <template v-else>
              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('profileCompany')" />
            </template>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-9">
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
              <div class="profile-save-icon h-100" v-if="($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR') && usePermissionM([3])">
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
              <div class="profile-save-icon h-100" v-if="($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR') && usePermissionM([3])">
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
              <div class="profile-save-icon h-100" v-if="($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR') && usePermissionM([3])">
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
                <span class="text-muted">{{ dataIDName?.location }}</span>
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
                <div class="text-muted mb-0" v-for="(advantage, index) in dataUserAdvantageEmployees" :key="advantage.id">
                  <img v-if="advantage?.advantage_employee.icon" style="margin-right: 7px;" width="18" height="18" class="mr-2" :src="`/storage/${advantage?.advantage_employee.icon}`">
                  {{ advantage?.advantage_employee.name_a }}
                </div>
              </template>
            </div>
            <div class="col-sm-1 text-end">
              <div class="profile-save-icon h-100" v-if="($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR') && usePermissionM([3])">
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
              <div class="profile-save-icon h-100" v-if="($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR') && usePermissionM([3])">
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
</template>


<script setup>
import { ref } from 'vue'
import { useLeafletConfig } from '@/Hooks/useLeafletConfig'
import { Required, Email, MinLength, Numeric, ImageExtension } from '@/Utils/validationMessages'
import { usePermissionM } from '@/Hooks/usePermissionM'
import useVuelidate from '@vuelidate/core'
import {
  setWhoAreWe,
  setIndustry,
  setContact,
  setAdvantage,
  setUserInfo,
  setCompanyInfo
} from '@/Services/CompanyProfileInfo.js'

const props = defineProps({
  title: String,
  info: Object,
  industries: Object,
  quantityEmployees: Object,
  companyTypes: Object,
  cities: Object,
  advantageEmployees: Object,
  currentYear: String,
  startYear: String,
  userAdvantageEmployees: Object,
  fieldOfActivities: Object,
  userFieldOfActivities: Object,
  userData: Object,
  companyData: Object,
})

const dataIDName = ref({
  industryName: props?.info?.industry?.name_i,
  quantityEmployeeName: props?.info?.quantity_employee?.name_q,
  foundationDate: props?.info?.foundation_date,
  companyTypeName: props?.info?.company_type?.name_c,
  cityName: props?.info?.city?.name_c,
  contactCityName: props?.info?.contactCity?.name_cc,
  location: props?.info?.location_l,
})

const userData = ref({
  name: props?.userData?.name,
  email: props?.userData?.email,
  phone: props?.userData?.phone,
  profile_photo_path: props?.userData?.profile_photo_path,
  password: '',
  password_confirmation: '',
  img: null,
  imgFile: null,
  img_remove: false
})

const userDataRules = {
  name: { Required },
  email: { Required, Email },
  phone: { Required },
  password: { MinLength: MinLength(7) },
  password_confirmation: { MinLength: MinLength(7) },
  imgFile: {
    ImageExtension
  },
};

const u$ = useVuelidate(userDataRules, userData);

console.log(props.companyData)

const companyData = ref({
  company_name: props?.companyData?.company_name,
  company_phone: props?.companyData?.company_phone,
  img: null,
  imgFile: null,
  company_photo_path: props?.companyData?.company_photo_path,
})

const companyDataRules = {
  company_name: { Required },
  company_phone: {  },
  imgFile: {
    ImageExtension
  },
};

const c$ = useVuelidate(companyDataRules, companyData);

const data = ref({
  who_are_we: {
    'ru': props?.info?.who_are_we_w
  },
  who_are_we: {
    'ru': props?.info?.who_are_we_w
  },
  who_are_we: {
    'ru': props?.info?.who_are_we_w
  },
  industry_id: props?.info?.industry_id,
  field_of_activities: [],
  quantity_employee_id: props?.info?.quantity_employee_id,
  company_type_id: props?.info?.company_type_id,
  city_id: props?.info?.city_id,
  contact_city_id: props?.info?.contact_city_id,
  email: props?.info?.email,
  company_site_url: props?.info?.company_site_url,
  foundation_date: props?.info?.foundation_date,
  lat: props?.info?.lat,
  lng: props?.info?.lng,
  location: {
    'ru': props?.info?.location_l
  }
})

const dataUserFieldOfActivities = ref(props?.userFieldOfActivities)
for(let p in props?.userFieldOfActivities) {
  data.value.field_of_activities.push(props?.userFieldOfActivities[p].field_of_activity?.id)
}

const dataUserAdvantageEmployees = ref(props?.userAdvantageEmployees)
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

const userProfileImg = ref(null)
const companyProfileImg = ref(null)

const userProfileImgPreview = ref(null)
const defaultUserProfileImg = ref(null)

const companyProfileImgPreview = ref(null)
const defaultCompanyProfileImg = ref(null)

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
  const info = await setWhoAreWe(data.value);
  if(info?.data) {
    data.value.who_are_we['ru'] = info?.data?.who_are_we?.ru
  }
  showBlock.value = ''
}

const setIndustryFunc = async () => {
  const row = await setIndustry(data.value)
  setNames(row)
}

const setContactFunc = async () => {
  const row = await setContact(data.value)
  setNames(row)
}

const setUserInfoFunc = async () => {
  u$.value.$touch();
  if (u$._value.$invalid) return false;

  if((userData.value.password || userData.value.password_confirmation) && userData.value.password != userData.value.password_confirmation) {
    return false
  }

  const row = await setUserInfo(userData.value, 'set_company_user_info')
  setUserProfileNames(row)
}

const setCompanyInfoFunc = async () => {
  c$.value.$touch();
  if (c$._value.$invalid) return false;

  const row = await setCompanyInfo(companyData.value, 'set_company_info')
  setCompanyProfileNames(row)
}

const setAdvantageFunc = async () => {
  const row = await setAdvantage(advantageEmployeeOptions.value)
  dataUserAdvantageEmployees.value = row.userAdvantageEmployees
  showBlock.value = ''
}

const setUserProfileNames = (row) => {
  if(row?.data) {
    userData.value.name               = row?.data?.name
    userData.value.email              = row?.data?.email
    userData.value.phone              = row?.data?.phone
    userData.value.profile_photo_path = row?.data?.profile_photo_path
    userData.value.img_remove         = false
    userProfileImgPreview.value       = null

    const img = row?.data?.profile_photo_path ? `/storage/${row?.data?.profile_photo_path}` : 'https://avatars.mds.yandex.net/i?id=0393e6b5b8874243329b425b83cc44b0e64b1b89-8986614-images-thumbs&n=13'
    document.getElementById("user-name").innerHTML = row?.data?.name
    document.getElementById("user-profile-img").src = img
  }
  showBlock.value = ''
}

const setCompanyProfileNames = (row) => {
  if(row?.data) {
    companyData.value.company_name       = row?.data?.company_name
    companyData.value.company_phone      = row?.data?.company_phone
    companyData.value.company_photo_path = row?.data?.company_photo_path
    companyData.value.img_remove         = false
    companyProfileImgPreview.value       = null

    if(companyData.value.company_name && document.querySelector("#invalid-message")) {
      document.querySelector("#invalid-message").remove();
    }
  }
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

    dataUserFieldOfActivities.value = row?.userFieldOfActivities

    dataIDName.value.location = row?.data?.location_l
  }
  showBlock.value = ''
}

const openUploadFile = (type) => {
  document.getElementById(type).click()
}

const updatePhotoPreview = (e, type) => {
  let file = ''

  if(type == 'userProfileImg') {
    file = userProfileImg.value.files[0]
  } else if(type == 'companyProfileImg') {
    file = companyProfileImg.value.files[0]
  }

  const reader = new FileReader();
  reader.onload = (e) => {

    if(type == 'userProfileImg') {
      userProfileImgPreview.value = e.target.result
      userData.value.img = e.target.result
      userData.value.imgFile = file
    } else if(type == 'companyProfileImg') {
      companyProfileImgPreview.value = e.target.result
      companyData.value.img = e.target.result
      companyData.value.imgFile = file
    }

    e.target.value = "";
  }

  reader.readAsDataURL(file);
}
</script>
