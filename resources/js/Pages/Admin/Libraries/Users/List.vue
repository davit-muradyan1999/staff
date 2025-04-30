<template>
  <Head :title="title" />
  <ModalDelete
    :show="deleteModal"
    @close="deleteModal.isOpen.value = false"
  />
  <AddUserModal
    v-if="addUserModal"
    :type="addUseType"
    :data="addUserData"
    :permissions="permissions"
    :companies="companies"
    :supportUsers="supportUsers"
    :supportCompanies="supportCompanies"
    :redirectURL="statusModalURL"
    @close="addUserModal = false"
  />
  <UserStatusModal
    v-if="statusModal"
    :user="statusModalUser"
    :redirectURL="statusModalURL"
    @close="statusModal = false"
  />
  <AddSalaryProjectModal
    v-if="workerRecipientModal"
    :user="workerRecipien"
    @close="workerRecipientModal = false"
  />
  <ProfileUserModal
    v-if="profileModal"
    :industries="industries"
    :quantityEmployees="quantityEmployees"
    :companyTypes="companyTypes"
    :cities="cities"
    :advantageEmployees="advantageEmployees"
    :currentYear="currentYear"
    :startYear="startYear"
    :user="profileModalUser"
    :fieldOfActivities="fieldOfActivities"
    :redirectURL="profileModalURL"
    @close="profileModal = false"
  />
  <ProfileMoreUserModal
    v-if="profileMoreModal"
    :user="profileMoreModalUser"
    :redirectURL="profileMoreModalURL"
    @close="profileMoreModal = false"
  />
  <ProfileMoreWorkerModal
    v-if="profileWorkerMoreModal"
    :user="profileWorkerMoreModalUser"
    :citizenships="citizenships"
    :genders="genders"
    :disabilityGroups="disabilityGroups"
    :currencies="currencies"
    :redirectURL="profileWorkerMoreModalURL"
    @close="profileWorkerMoreModal = false"
  />
  <ShowBrancheListModal
    v-if="brancheListModal"
    :user="brancheListModalUser"
    @close="brancheListModal = false"
  />
  <div class="row align-items-center mb-4">
    <div class="col-md-3 mb-3">
      <div class="input-group">
        <input
          v-model="search.text"
          class="form-control border"
          type="text"
          id="example-search-input"
          :placeholder="`${$t('general.search')}`"
        >
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <Multiselect
        valueProp="value"
        v-model="search.status"
        :options="userStatusData.statuses"
        :searchable="true"
        label="name"
        track-by="name"
        :placeholder="`${$t('general.status')}`"
      />
    </div>
    <div class="col-md-3 mb-3" v-if="pageRole == 'MODERATOR' || pageRole == 'COMPANY'">
      <Multiselect
        v-model="search.company_id"
        valueProp="id"
        :options="companies"
        :searchable="true"
        label="company_name"
        track-by="company_name"
        @change="changeCompany"
        :placeholder="`${$t('general.company')}`"
      />
    </div>
    <div class="col-md-3 mb-3" v-if="pageRole == 'MODERATOR'">
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
    <div class="col-md-3 mb-3">
      <div class="row align-items-center">
        <div class="col-1">
          <input type="checkbox" class="form-check-input" id="is_deleted" v-model="search.is_deleted" true-value="1" false-value="0">
        </div>
        <label for="is_deleted" class="col-11 col-form-label">{{ $t("general.deletedOnes") }}</label>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row bg-white align-items-center">
        <div class="col-6">
          <span class="fw-bold text-success">
            {{ title }} / {{
              pageRole == 'WORKER'
              ? $t("general.employees")
              : pageRole == 'COMPANY'
                ? $t("general.companies") : pageRole == 'MODERATOR'
                  ? $t("general.moderators")
                  : $t("general.administrators")
            }}
          </span>
        </div>
        <div class="col-6">
          <button type="button" class="btn float-end btn-success" @click="addUser(pageRole)">
            <font-awesome-icon icon="plus" />
            {{ $t("general.add") }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="table-responsive table-list">
      <div class="card mt-3" v-for="user in users.data" :key="user.id">
        <div class="card-body p-3 position-relative">
          <div class="row bg-white align-items-center">
            <div class="col-md-1 border-end">{{ user.id }}</div>

            <div class="col-md-1 border-end">
              <img v-if="(pageRole == 'WORKER' || pageRole == 'MODERATOR' || pageRole == 'ADMINISTRATOR') && user.profile_photo_path" width="50" height="50" class="rounded-circle me-1" :src="'/storage/' + user.profile_photo_path" alt="">
              <img v-else-if="pageRole == 'COMPANY' && user.company_photo_path" width="50" height="50" class="rounded-circle me-1" :src="'/storage/' + user.company_photo_path" alt="">
              <img v-else width="50" height="50" class="rounded-circle me-1" :src="'/images/user.png'" alt="">
            </div>

            <div class="col border-end">
              <template v-if="pageRole == 'COMPANY'">
                {{ user.name }}
              </template>
              <template v-else-if="pageRole == 'WORKER'">
                <a class="text-decoration-none" target="_blank" :href="route('profile', { id: user?.id })">
                  {{ user.name }}
                </a>
              </template>
              <template v-else>
                {{ user.name }}
              </template>
            </div>

            <template v-if="pageRole == 'COMPANY' || pageRole == 'MODERATOR'">
              <div class="col border-end">
                <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: user?.id })">
                  {{ user.company_name || '-' }}
                </a>
              </div>
              <div class="col border-end">{{ user.company_phone || '-' }}</div>
            </template>
            <template v-if="pageRole == 'MODERATOR'">

              <div class="col border-end">
                <span class="cursor-pointer text-primary" @click="openBrancheListModal(user)">{{ $t("general.look") }}</span>
              </div>
              </template>

              <div class="col border-end">{{ user.phone || '-' }}</div>

              <div class="col border-end" v-if="pageRole == 'WORKER'">
                <span :class="{'text-warning': user.status == 'PROGRESS', 'text-success': user.status == 'CONFIRMED', 'text-danger': user.status == 'REJECTED'}">
                  <template v-if="user.status == 'PROGRESS'">{{ $t("general.progress") }}</template>
                  <template v-else-if="user.status == 'CONFIRMED'">{{ $t("general.confirmed") }}</template>
                  <template v-else-if="user.status == 'REJECTED'">{{ $t("general.rejected") }}</template>
                </span>
                <template v-if="!user.status">
                  -
                </template>
              </div>

              <div class="col border-end">{{ user.created_at || '-' }}</div>

              <div class="col actions">
                <font-awesome-icon icon="pen-to-square" class="me-2 edit" @click="addUser(pageRole, user)" />

                <font-awesome-icon
                  v-if="pageRole == 'WORKER'"
                  style="color: #097f8f"
                  icon="id-card-clip"
                  class="check me-2"
                  @click="openWorkerProfileMoreModal(user)"
                />

                <font-awesome-icon
                  v-if="pageRole == 'WORKER'"
                  icon="user-check"
                  class="check me-2"
                  @click="openStatusModal(user)"
                />

                <font-awesome-icon
                  v-if="pageRole == 'WORKER' && user.status == 'CONFIRMED'"
                  icon="building-columns"
                  class="check me-2"
                  @click="openWorkerRecipientModal(user)"
                />

                <font-awesome-icon
                  v-if="pageRole == 'COMPANY'"
                  style="color: #0067c5"
                  icon="address-card"
                  class="check me-2"
                  @click="openProfileModal(user)"
                />

                <font-awesome-icon
                  v-if="pageRole == 'COMPANY'"
                  style="color: #097f8f"
                  icon="id-card-clip"
                  class="check me-2"
                  @click="openProfileMoreModal(user)"
                />

                <font-awesome-icon
                  style="display: none;"
                  icon="trash"
                  class="trash"
                  @click="
                    (deleteModal.isOpen.value = true),
                    (deleteModal.data = user),
                    (deleteModal.url.value = 'admin.territory_types.destroy')
                  "
                />
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="paginate-block-end mt-3">
    <Pagination :links="users?.links" />
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { useModalDelete } from '@/Hooks/useModalDelete'
import { ref, watch } from 'vue'
import UserStatusModal from '@/Components/UserStatusModal.vue'
import AddSalaryProjectModal from '@/Components/AddSalaryProjectModal.vue'
import ProfileUserModal from '@/Components/ProfileUserModal.vue'
import ProfileMoreUserModal from '@/Components/ProfileUserMoreModal.vue'
import ProfileMoreWorkerModal from '@/Components/ProfileWorkerMoreModal.vue'
import AddUserModal from './AddUserModal.vue'
import ShowBrancheListModal from './ShowBrancheListModal.vue'
import { useUserStatuses } from '@/Hooks/useUserStatuses'
import { router } from '@inertiajs/vue3'

defineOptions({ layout: Layout })

defineProps({
  title: String,
  users: Object,
  companies: Object,
  branches: Object,
  permissions: Object,
  companies: Object,
  citizenships: Object,
  supportUsers: Object,
  supportCompanies: Object,

  currencies: Object,

  industries: Object,
  quantityEmployees: Object,
  companyTypes: Object,
  cities: Object,
  genders: Object,
  disabilityGroups: Object,
  advantageEmployees: Object,
  fieldOfActivities: Object,
  currentYear: String,
  startYear: String,
})

const urlParams = new URLSearchParams(window.location.search)

const pageRole = urlParams.get('role')
const stage = urlParams.get('stage')
const statusModal = ref(false)
const statusModalUser = ref({})
const statusModalURL = ref('')

const workerRecipientModal = ref(false)
const workerRecipien = ref({})

const profileModal = ref(false)
const profileModalUser = ref({})
const profileModalURL = ref('')

const profileMoreModal = ref(false)
const profileMoreModalUser = ref({})
const profileMoreModalURL = ref('')

const profileWorkerMoreModal = ref(false)
const profileWorkerMoreModalUser = ref({})
const profileWorkerMoreModalURL = ref('')

const brancheListModal = ref(false)
const brancheListModalUser = ref({})

const addUserModal = ref(false)
const addUseType = ref('')
const addUserData = ref({})

const selectedJobID = ref('')
let selectedBranches = []

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
}

const userStatusData = useUserStatuses()
const deleteModal = useModalDelete()

const search = ref({
  text: urlParams.get('text'),
  status: urlParams.get('status'),
  company_id: urlParams.get('company_id'),
  branches: selectedBranches,
  is_deleted: urlParams.get('is_deleted'),
});

const addUser = (type, user = {}) => {
  addUserModal.value = true
  addUseType.value = type
  addUserData.value = user
  statusModalURL.value = window.location.href
}

const openBrancheListModal = (user) => {
  brancheListModal.value = true
  brancheListModalUser.value = user
}

const openStatusModal = (user) => {
  statusModal.value = true
  statusModalUser.value = user
  statusModalURL.value = window.location.href
}

const openWorkerRecipientModal = (user) => {
  workerRecipientModal.value = true
  workerRecipien.value = user
}

const openProfileModal = (user) => {
  profileModal.value = true
  profileModalUser.value = user
  profileModalURL.value = window.location.href
}

const openProfileMoreModal = (user) => {
  profileMoreModal.value = true
  profileMoreModalUser.value = user
  profileMoreModalURL.value = window.location.href
}

const openWorkerProfileMoreModal = (user) => {
  profileWorkerMoreModal.value = true
  profileWorkerMoreModalUser.value = user
  profileWorkerMoreModalURL.value = window.location.href
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.users.index'), {
    stage: stage,
    role: pageRole,
    search: true,
    text: currentValue.text,
    status: currentValue.status,
    company_id: currentValue.company_id,
    branches: currentValue.branches,
    is_deleted: currentValue.is_deleted,
  }, {
    preserveState: true,
    replace: true
  })
})

const changeCompany = (e) => {
  search.value.branches = []
}
</script>

