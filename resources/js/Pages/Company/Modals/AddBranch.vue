<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addBranchModal"
      tabindex="-1"
      aria-labelledby="addBranchModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ !data?.id ? $t("general.addBranch") : $t("general.editBranch") }}</h4>
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
              <template v-for="(lang, index) in $page.props.languages" :key="lang.id">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-2">
                        <label for="title" class="col-sm-2 col-form-label">{{ $t("general.title") }}*</label>
                      </div>
                      <div class="col-md-10">
                        <input
                          type="text"
                          id="title"
                          class="form-control"
                          v-model="form.title[lang.code]"
                          :class="{ 'is-invalid': v$?.title?.[lang.code]?.$errors.length }"
                        >
                        <div class="invalid-feedback">
                          {{ v$?.title?.[lang.code]?.$errors?.[0]?.$message || v$?.title?.[lang.code]?.$errors?.[0]?.$params?.message }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-2">
                        <label for="city" class="col-sm-2 col-form-label">{{ $t("general.city") }}*</label>
                      </div>
                      <div class="col-md-10">
                        <input
                          type="text"
                          id="city"
                          class="form-control"
                          v-model="form.city[lang.code]"
                          :class="{ 'is-invalid': v$?.city?.[lang.code]?.$errors.length }"
                        >
                        <div class="invalid-feedback">
                          {{ v$?.city?.[lang.code]?.$errors?.[0]?.$message || v$?.city?.[lang.code]?.$errors?.[0]?.$params?.message }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-2">
                        <label for="phone" class="col-sm-2 col-form-label">{{ $t("general.phone") }}*</label>
                      </div>
                      <div class="col-md-10">
                        <input
                          type="text"
                          id="phone"
                          class="form-control"
                          v-model="form.phone[lang.code]"
                          :class="{ 'is-invalid': v$?.phone?.[lang.code]?.$errors.length }"
                        >
                        <div class="invalid-feedback">
                          {{ v$?.phone?.[lang.code]?.$errors?.[0]?.$message || v$?.phone?.[lang.code]?.$errors?.[0]?.$params?.message }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-2">
                        <label for="address" class="col-sm-2 col-form-label">{{ $t("general.address") }}*</label>
                      </div>
                      <div class="col-md-10">
                        <input
                          type="text"
                          id="address"
                          class="form-control"
                          v-model="form.address[lang.code]"
                          :class="{ 'is-invalid': v$?.address?.[lang.code]?.$errors.length }"
                        >
                        <div class="invalid-feedback">
                          {{ v$?.address?.[lang.code]?.$errors?.[0]?.$message || v$?.address?.[lang.code]?.$errors?.[0]?.$params?.message }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="text-center">
                      <p>Кликните два раза на карту чтобы отметить местонахождение филиала*</p>
                    </div>
                    <div style="height: 300px; width: 100%;">
                      <l-map ref="map" :zoom="mapConfig.zoom" :center="mapConfig.center" @dblclick="addMarker"  :minZoom="3" :maxZoom="18" :zoomAnimation="true">
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
                        <l-marker v-if="form.lat && form.lng" :lat-lng="[form.lat, form.lng]" :draggable="true" @dragend="updateMarker"></l-marker>
                      </l-map>
                    </div>
                    <div class="invalid-feedback" :style="{display: v$?.lat?.$errors?.[0]?.$message || v$?.lat?.$errors?.[0]?.$params?.message ? 'block' : 'none'}">
                      {{ v$?.lat?.$errors?.[0]?.$message || v$?.lat?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>
                </div>
              </template>
            </div>
            <div class="modal-footer">
              <input type="submit" class="btn btn-primary" :value="!data?.id ? $t('general.add') : $t('general.edit')" />
              <button type="button" class="btn btn-danger" @click.prevent="deleteBranch">{{ $t('general.delete') }}</button>
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
import { Required, Email, MinLength } from '../../../Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { useLeafletConfig } from '@/Hooks/useLeafletConfig'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  data: Object
});

const emit = defineEmits(['close'])

const mapConfig = useLeafletConfig()
const map = ref(null)
const marker = ref([])
const errorMsg = ref('')

const form = useForm({
  id: props?.data?.id,
  title: {
    'ru': props?.data?.title_n,
  },
  city: {
    'ru': props?.data?.city_n,
  },
  phone: {
    'ru': props?.data?.phone_n,
  },
  address: {
    'ru': props?.data?.address_n,
  },
  lat: props?.data?.lat,
  lng: props?.data?.lng,
});

const rules = {
  title: {
    ru: { Required },
  },
  city: {
    ru: { Required },
  },
  phone: {
    ru: { Required },
  },
  address: {
    ru: { Required },
  },
  lat: { Required },
  lng: { Required },
};

const v$ = useVuelidate(rules, form);

const addMarker = (event) => {
  if(event.latlng) {
    form.lat = event.latlng.lat
    form.lng = event.latlng.lng
  }
}

const updateMarker = (event) => {
  if(event.target?._latlng) {
    form.lat = event.target?._latlng?.lat
    form.lng = event.target?._latlng?.lng
  }
}

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  const routeUrl = props?.data?.id ? route('update_branch') : route('add_branch')

  axios.post(routeUrl, form).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('my_branches'))
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const deleteBranch = () => {
  if (confirm('Подтвердить удаление?') == true) {
    axios.post(route('delete_branch'), form).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('my_branches'))
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
  }
}

const closeModal = () => {
  emit("close")
}

</script>
