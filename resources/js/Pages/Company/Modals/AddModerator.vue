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
              <h4 class="modal-title">{{ !data?.id ? $t("general.addModerator") : $t("general.editModerator") }}</h4>
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
                <div class="col-md-2">
                  <input
                    type="text"
                    id="title"
                    class="form-control"
                    v-model="form.name"
                    :class="{ 'is-invalid': v$?.name?.$errors.length }"
                    :placeholder="`${$t('general.employeeName')}`"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.name?.$errors?.[0]?.$message || v$?.name?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-2">
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
                <div class="col-md-2">
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
                <div class="col-md-3">
                  <Multiselect
                    v-model="form.permission_ids"
                    mode="tags"
                    valueProp="id"
                    :options="permissions"
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

                <template v-if="true">
                  <div class="col-md-6">
                    <input
                      type="text"
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

                  <div class="col-md-6">
                    <input
                      type="text"
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
            <div class="modal-footer text-center">
              <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary me-3">{{ !data?.id ? $t('general.save') : $t('general.edit') }}</button>
                <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('general.revoke') }}</button>
              </div>
              <div class="col-md-12 text-center" v-if="data?.id">
                <a href="#" class="text" @click.prevent="deleteModerator"><i>{{ $t('general.deleteModerator') }}</i></a>
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
import { Required, Email, MinLength } from '../../../Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  data: Object,
  branches: Object,
  permissions: Object,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

let brancheIds = []
let permissionIds = []

if(props?.data?.branches) {
  for(let branch in props?.data?.branches)
    brancheIds.push(props?.data?.branches?.[branch]?.pivot?.branche_id)
}

if(props?.data?.permissions) {
  for(let permission in props?.data?.permissions)
    permissionIds.push(props?.data?.permissions?.[permission]?.pivot?.permission_id)
}

const form = useForm({
  id: props?.data?.id,
  name: props?.data?.name,
  branche_ids: brancheIds,
  permission_ids: permissionIds,
  email: props?.data?.email,
  phone: props?.data?.phone,
  password: '',
  password_confirmation: '',
});

let passwordRules = {MinLength: MinLength(7)}

if(!props?.data?.id) {
  passwordRules = { Required, MinLength: MinLength(7) }
}

const rules = {
  name: { Required },
  branche_ids: { Required },
  permission_ids: { Required },
  email: { Required, Email },
  phone: { Required },
  password: passwordRules,
  password_confirmation: passwordRules,
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  const routeUrl = props?.data?.id ? route('update_moderator') : route('add_moderator')

  axios.post(routeUrl, form).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('moderators'))
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
    axios.post(route('delete_moderator'), form).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('moderators'))
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
  }
}

const closeModal = () => {
  emit("close")
}

</script>
