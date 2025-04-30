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
              <h4 class="modal-title">{{ !data?.id ? $t("general.add") : $t("general.edit") }}</h4>
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
                <div class="col-md-4">
                  <input
                    type="text"
                    id="title"
                    class="form-control"
                    v-model="form.name"
                    :class="{ 'is-invalid': v$?.name?.$errors.length }"
                    :placeholder="`${$t('general.firstName')}`"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.name?.$errors?.[0]?.$message || v$?.name?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4" v-if="type == 'COMPANY'">
                  <input
                    type="text"
                    id="company-name"
                    class="form-control"
                    v-model="form.company_name"
                    :class="{ 'is-invalid': v$?.company_name?.$errors.length }"
                    :placeholder="`${$t('general.companyName')}`"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.company_name?.$errors?.[0]?.$message || v$?.company_name?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4" v-if="type == 'MODERATOR'">
                  <Multiselect
                    v-model="form.company_id"
                    valueProp="id"
                    :options="companies"
                    :searchable="true"
                    label="company_name"
                    track-by="company_name"
                    :class="{ 'is-invalid': v$.company_id.$errors.length }"
                    :placeholder="`${$t('general.company')}`"
                    @change="changeCompany"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.company_id?.$errors?.[0]?.$message || v$?.company_id?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4" v-if="type == 'MODERATOR'">
                  <Multiselect
                    v-model="form.branche_ids"
                    mode="tags"
                    valueProp="id"
                    :options="branches"
                    :searchable="true"
                    label="title_b"
                    track-by="title_b"
                    :class="{ 'is-invalid': v$.branche_ids.$errors.length }"
                    :placeholder="`${$t('general.branches')}`"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.branche_ids?.$errors?.[0]?.$message || v$?.branche_ids?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4">
                  <input
                    type="text"
                    id="email"
                    class="form-control"
                    v-model="form.email"
                    :class="{ 'is-invalid': v$?.email?.$errors.length }"
                    :placeholder="`${$t('general.email')}`"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.email?.$errors?.[0]?.$message || v$?.email?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4">
                  <input
                    type="text"
                    id="phone"
                    class="form-control"
                    v-model="form.phone"
                    :class="{ 'is-invalid': v$?.phone?.$errors.length }"
                    :placeholder="`${$t('general.phoneNumber')}`"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.phone?.$errors?.[0]?.$message || v$?.phone?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4" v-if="type == 'COMPANY'">
                  <input
                    type="text"
                    id="company-phone"
                    class="form-control"
                    v-model="form.company_phone"
                    :class="{ 'is-invalid': v$?.company_phone?.$errors.length }"
                    :placeholder="`${$t('general.companyPhone')}`"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.company_phone?.$errors?.[0]?.$message || v$?.company_phone?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-4" v-if="type == 'MODERATOR' || type == 'ADMINISTRATOR'">
                  <Multiselect
                    v-model="form.permission_ids"
                    mode="tags"
                    valueProp="id"
                    :options="
                      permissions.filter(function (el) {
                        return el.type == type
                      })
                    "
                    :searchable="true"
                    label="name_p"
                    track-by="name_p"
                    :class="{ 'is-invalid': v$.permission_ids.$errors.length }"
                    :placeholder="`${$t('general.roles')}`"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.permission_ids?.$errors?.[0]?.$message || v$?.permission_ids?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>

                <div class="col-md-4" v-if="(type == 'WORKER' || type == 'COMPANY' || type == 'MODERATOR') && usePermissionA([])">
                  <Multiselect
                    v-model="form.user_support_ids"
                    mode="tags"
                    valueProp="id"
                    :options="type == 'WORKER' ? supportUsers : supportCompanies"
                    :searchable="true"
                    label="name"
                    track-by="name"
                    :class="{ 'is-invalid': v$.user_support_ids.$errors.length }"
                    :placeholder="`${$t('general.supportSpecialist')}`"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.user_support_ids?.$errors?.[0]?.$message || v$?.user_support_ids?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>

                <template v-if="true">
                  <div class="col-md-4">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      v-model="form.password"
                      :class="{ 'is-invalid': v$?.password?.$errors.length }"
                      :placeholder="`${$t('general.password')}`"
                    >
                    <div class="invalid-feedback">
                      {{ v$?.password?.$errors?.[0]?.$message || v$?.password?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>

                  <div class="col-md-4">
                    <input
                      type="password"
                      id="password_confirmation"
                      class="form-control"
                      v-model="form.password_confirmation"
                      :class="{ 'is-invalid': v$?.password_confirmation?.$errors.length }"
                      :placeholder="`${$t('general.confirmPassword')}`"
                    >
                    <div class="invalid-feedback">
                      {{ v$?.password_confirmation?.$errors?.[0]?.$message || v$?.password_confirmation?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>
                </template>
              </div>

            </div>
            <div class="modal-footer">
              <div class="col-md-12 col-12 action-block">
                <div class="row">
                  <div class="col-md-6 col-12 text-end">
                    <button type="submit" class="btn btn-primary me-3 mb-2">{{ !data?.id ? $t('general.save') : $t('general.edit') }}</button>
                  </div>
                  <div class="col-md-6 col-12 text-start">
                    <button type="button" class="btn btn-secondary mb-2" @click="closeModal">{{ $t('general.revoke') }}</button>
                  </div>
                </div>
              </div>
              <div class="col-md-12 col-12 text-center" v-if="data?.id">
                <a href="#" class="text" @click.prevent="deleteModerator"><i>{{ $t('general.delete') }}</i></a>
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
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import { getBranchesByCompanyId } from '@/Services/Misc.js'
import { usePermissionA } from '@/Hooks/usePermissionA'

const props = defineProps({
  type: String,
  data: Object,
  companies: Object,
  permissions: Object,
  supportUsers: Object,
  supportCompanies: Object,
  redirectURL: String,
});

const emit = defineEmits(['close'])

const branches = ref(props?.branches)
const permissions = ref(props?.permissions)

const errorMsg = ref('')

let brancheIds = []
let permissionIds = []
let userSupportIds = []

if(props?.data?.branches) {
  for(let branch in props?.data?.branches)
    brancheIds.push(props?.data?.branches?.[branch]?.pivot?.branche_id)
}

if(props?.data?.permissions) {
  for(let permission in props?.data?.permissions)
    permissionIds.push(props?.data?.permissions?.[permission]?.pivot?.permission_id)
}

if(props?.data?.user_supports) {
  for(let support in props?.data?.user_supports)
  userSupportIds.push(props?.data?.user_supports?.[support]?.pivot?.support_user_id)
}

const form = useForm({
  _method: props?.data?.id ? 'PUT' : 'POST',
  id: props?.data?.id,
  type: props?.type,
  name: props?.data?.name,
  company_name: props?.data?.company_name,
  company_id: props?.data?.parent_user_id,
  branche_ids: brancheIds,
  permission_ids: permissionIds,
  user_support_ids: userSupportIds,
  email: props?.data?.email,
  phone: props?.data?.phone,
  company_phone: props?.data?.company_phone,
  password: '',
  password_confirmation: '',
});

let passwordRules = {MinLength: MinLength(7)}

if(!props?.data?.id) {
  passwordRules = { Required, MinLength: MinLength(7) }
}

const validationCompanyName = props?.type == 'COMPANY' ? { Required } : { }
const validationCompanyId = props?.type == 'MODERATOR' ? { Required } : { }
const validationCompanyPhone = props?.type == 'COMPANY' ? { Required } : { }
const validationBranch = props?.type == 'MODERATOR' ? { Required } : { }
const validationPermission = props?.type == 'MODERATOR' ? { Required } : { }

const rules = {
  name: { Required },
  company_name: validationCompanyName,
  company_id: validationCompanyId,
  branche_ids: validationBranch,
  permission_ids: validationPermission,
  user_support_ids: { },
  email: { Required, Email },
  phone: { Required },
  company_phone: validationCompanyPhone,
  password: passwordRules,
  password_confirmation: passwordRules,
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  const routeUrl = props?.data?.id ? route('admin.users.update', props?.data?.id) : route('admin.users.store')

  axios.post(routeUrl, form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    closeModal()
    router.get(props?.redirectURL)
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const deleteModerator = () => {
  if (confirm('Подтвердить удаление?') == true) {
    axios.delete(route('admin.users.destroy', form?.id)).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(props?.redirectURL)
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
  }
}

const closeModal = () => {
  emit("close")
}

const changeCompany = async (value, firstCall = false) => {
  if(!firstCall)
    form.branche_ids = []

  const branchesData = await getBranchesByCompanyId(value)
  branches.value = branchesData
}

changeCompany(props?.data?.parent_user_id, true)

</script>
<style scoped>
@media only screen and (max-width: 773px) {
  .action-block button {
    width: 100%;
  }
}
</style>
