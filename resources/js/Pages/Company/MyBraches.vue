<template>
  <Head :title="title" />
  <AddBranch
    v-if="openAddBranchPopup"
    :data="selectedBranch"
    @close="openAddBranchPopup = false"
  />
  <ModeratorList
    v-if="openModeratorListPopup"
    :branche="selectedModeratorBranch"
    @close="openModeratorListPopup = false"
  />
  <h2 class="text-center mb-3">{{ $t("general.branches") }} {{ branchesCount }}</h2>
  <div class="text-end" v-if="usePermissionM([])">
    <button :disabled="$page.props.auth?.user?.role == 'COMPANY' && !$page.props.auth?.user?.company_name" class="btn btn-success mt-2" type="button" @click="openAddBranchPopup = true, selectedBranch = {}">{{ $t("general.addBranch") }}</button>
  </div>
  <div class="row align-items-center">
    <div class="container mt-4">
      <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 position-relative" v-for="(branch, index) in branches" :key="branch.id">
          <div class="card h-100">
            <div class="card-body">
              <div class="row h-100">
                <div class="col-12">
                  <button type="button" class="btn btn-warning card-branch-name rounded-button-bottom">{{ branch.title_n }}</button>
                  <font-awesome-icon
                    v-if="usePermissionM([])"
                    icon="pen-to-square"
                    class="fs-3 cursor-pointer float-end"
                    style="font-size: 20px !important; color: #004eff;"
                    @click="editBranch(branch)"
                  />
                </div>
                <div class="col-6">
                  <strong>Город</strong>
                </div>
                <div class="col-6">
                  {{ branch.city_n }}
                </div>
                <div class="col-6">
                  <strong>Адрес</strong>
                </div>
                <div class="col-6">
                  {{ branch.address_n }}
                </div>
                <div class="col-6">
                  <strong>Телефон</strong>
                </div>
                <div class="col-6">
                  {{ branch.phone_n }}
                </div>
                <div class="col-12">
                  <span class="text-decoration-underline cursor-pointer" @click="openModeratorList(branch)"><strong>Модератор</strong></span>
                </div>
                <div class="col-12 align-self-end mt-2">
                  <div style="height: 250px;">
                    <l-map ref="map" :zoom="mapConfig.zoom" :center="[branch.lat, branch.lng]" :minZoom="3" :maxZoom="18" :zoomAnimation="true">
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
                      <l-marker v-if="branch.lat && branch.lng" :lat-lng="[branch.lat, branch.lng]"></l-marker>
                    </l-map>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref } from 'vue'
import { useLeafletConfig } from '@/Hooks/useLeafletConfig'
import AddBranch from './Modals/AddBranch.vue'
import ModeratorList from './Modals/ModeratorList.vue'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineOptions({ layout: Layout })

const mapConfig = useLeafletConfig()
const map = ref(null)

const openAddBranchPopup = ref(false)
const selectedBranch = ref({})

const openModeratorListPopup = ref(false)
const selectedModeratorBranch = ref({})

const props = defineProps({
  title: String,
  branches: Object,
  branchesCount: Number
});

const editBranch = (data) => {
  openAddBranchPopup.value = true
  selectedBranch.value = data
}

const openModeratorList = (branche) => {
  openModeratorListPopup.value = true
  selectedModeratorBranch.value = branche
}

</script>
