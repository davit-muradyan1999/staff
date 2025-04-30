<template>
  <Head :title="title" />
  <AddModerator
    v-if="openAddModeratorPopup"
    :data="selectedModerator"
    :branches="branches"
    :permissions="permissions"
    @close="openAddModeratorPopup = false"
  />
  <div class="row align-items-center">
    <div class="col-md-3 mb-2">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <Multiselect
        v-model="search.branches"
        valueProp="id"
        :options="branches"
        :searchable="true"
        mode="tags"
        label="title_b"
        track-by="title_b"
        :placeholder="`${$t('general.branches')}`"
      />
    </div>
    <div class="col-md-2 mb-2">
      <Multiselect
        v-model="search.roles"
        valueProp="id"
        :options="permissions"
        :searchable="true"
        mode="tags"
        label="name_p"
        track-by="name_p"
        :placeholder="`${$t('general.role')}`"
      />
    </div>
  </div>
  <div class="mt-3" v-if="false">
    <button class="btn btn-primary ps-3 pe-3 pt-2 pb-2 me-3 mb-3 no-border-outline all-braches-button" :class="{'active': allBranchesSelected}" type="button" @click="setAllBranches">
      {{ $t('general.allBranches') }}
    </button>
    <div class="btn-group-toggle d-inline-block me-2" data-toggle="buttons" v-for="branche in branches" :key="branche.id">
      <input
        type="checkbox"
        v-model="search.branches"
        :true-value="[]"
        :value="branche.id"
        class="btn-check no-border-outline"
        :id="`btn-check-${branche.id}`"
        autocomplete="off"
      >
      <label class="btn btn-primary ps-3 pe-3 pt-2 pb-2 me-3 mb-3 no-border-outline" :class="{'choosed-filter-button': search.branches.includes(branche.id)}" :for="`btn-check-${branche.id}`">
        {{ branche.title_b }}
      </label>
    </div>
  </div>
   <div class="mt-3 text-end">
    <button v-if="usePermissionM([3])" :disabled="$page.props.auth?.user?.role == 'COMPANY' && !$page.props.auth?.user?.company_name" @click="openAddModeratorPopup = true, selectedModerator = {}" class="btn btn-success ps-3 pe-3 pt-2 pb-2" type="button">
      {{ $t('general.addModerator') }}
    </button>
  </div>

  <div class="mt-3">
    <div class="table-responsive table-list-medium">
      <div class="card mt-3" v-for="moderator in moderators" :key="moderator.id">
        <div class="card-body p-4 position-relative">
          <div class="row bg-white">
            <div class="col border-end">
              {{ moderator?.name }}
            </div>
            <div class="col border-end">
              <span v-for="(branch, index) in moderator?.branches" :key="index">
                {{ branch?.title_mb }}{{ index + 1 == moderator?.branches?.length ? '' : ', ' }}
              </span>
            </div>
            <div class="col border-end">
              {{ moderator?.email }}
            </div>
            <div class="col border-end">
              {{ moderator?.phone }}
            </div>
            <div class="col border-end">
              <span v-for="(branch, index) in moderator?.permissions" :key="index">
                {{ branch?.name_p }}{{ index + 1 == moderator?.permissions?.length ? '' : ', ' }}
              </span>
            </div>
            <div class="col" v-if="usePermissionM([3])">
              <font-awesome-icon
                icon="pen-to-square"
                class="edit-pencil"
                @click="editModerator(moderator)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, watch } from 'vue'
import AddModerator from './Modals/AddModerator.vue'
import { router } from '@inertiajs/vue3'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const allBranchesSelected = ref(true)

let selectedRoles = []
let selectedBranches = []

if([...urlParams.getAll('roles[]')].length > 0) {
  for(let roleID in [...urlParams.getAll('roles[]')]) {
    selectedRoles.push([...urlParams.getAll('roles[]')][roleID])
  }
}

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
  allBranchesSelected.value = false
}

const search = ref({
  text: urlParams.get('text'),
  roles: selectedRoles,
  branches: selectedBranches,
});

const props = defineProps({
  title: String,
  moderators: Object,
  branches: Object,
  permissions: Object
});

const selectedModerator = ref({})
const openAddModeratorPopup = ref(false)

const editModerator = (data) => {
  openAddModeratorPopup.value = true
  selectedModerator.value = data
}

const setAllBranches = () => {
  search.value.branches = []
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('moderators'), {
    search: true,
    text: currentValue.text,
    roles: currentValue.roles,
    branches: currentValue.branches,
  }, {
    preserveState: true
  })

  allBranchesSelected.value = currentValue.branches.length > 0 ? false : true
});

</script>
