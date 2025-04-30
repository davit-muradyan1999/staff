<template>
  <div class="row">
    <div class="col-lg-3">
      <div class="card mb-4">
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

          <div class="profile-save-icon" v-if="$page.props.auth?.user?.role == 'WORKER'" style="position: absolute; top: 10px; right: 10px;">
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
    </div>
    <div class="col-lg-9">
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Опыт с нами</p>
            </div>
            <div class="col-sm-9">
              <p class="text-muted mb-0">Занимал 5 должностей, отработал 658 часов</p>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Предпочитаемые должности</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'preferredPositions'">
                <Multiselect
                  v-model="preferredPositions"
                  mode="tags"
                  valueProp="id"
                  :options="positions"
                  :searchable="true"
                  label="name_p"
                  track-by="name_p"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(position, index) in dataUserPositions" :key="position.id">
                  {{ position?.position.name_p }}{{ index + 1 == dataUserPositions.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'preferredPositions'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setPreferredPositionFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('preferredPositions')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('preferredPositions')" />
                </template>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Опыт работы</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'experience'">
                <div class="row">
                  <template v-for="(data, index) in experienceDatas" :key="index">
                    <div class="col-md-10 col-sm-12 mt-3">
                      <div class="row">
                        <div class="col-md-6 mb-2">
                          <input type="text" v-model="data.organization['ru']" id="organization" class="form-control" :placeholder="`${$t('general.organization')}*`">
                        </div>
                        <div class="col-md-6 mb-2">
                          <input type="text" v-model="data.position['ru']" id="position" class="form-control" :placeholder="`${$t('general.position')}*`">
                        </div>

                        <div class="col-md-6 mb-2">
                          <DatePicker v-model="data.started_at" :model-config="pickerConfig" :masks="masks" mode="date" locale="ru">
                            <template v-slot="{ inputValue, inputEvents }">
                              <input
                                class="form-control"
                                id="inputClothingSize"
                                :value="inputValue"
                                v-on="inputEvents"
                                :placeholder="`${$t('general.for')}*`"
                              />
                            </template>
                          </DatePicker>
                        </div>
                        <div class="col-md-6 mb-2">
                          <DatePicker v-model="data.finished_at" :model-config="pickerConfig" :masks="masks" mode="date" locale="ru">
                            <template v-slot="{ inputValue, inputEvents }">
                              <input
                                class="form-control"
                                id="inputClothingSize"
                                :value="inputValue"
                                v-on="inputEvents"
                                :placeholder="`${$t('general.unto')}`"
                                :disabled="data.is_active_work ? true : false"
                              />
                            </template>
                          </DatePicker>
                        </div>

                        <div class="col-md-12 mb-1">
                          <label class="flex items-center">
                            <input type="checkbox" @change="isActiveWorkFunc($event, index)" v-model="data.is_active_work" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 me-2" name="remember" />
                            <span class="ml-2 text-sm text-gray-600">На данный момент я работаю в этой должности</span>
                          </label>
                        </div>
                      </div>
                      <div class="col-md-12 mb-4">
                        <div class="invalid-feedback" v-if="data?.started_at && data?.finished_at && setExperienceDays(data?.started_at, data?.finished_at, index) <= 0" style="display: block;">Проверьте правильность дат</div>
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-12 mt-3">
                      <button v-if="index == 0" type="button" class="btn btn-primary btn-sm" @click="addExperience">
                        <font-awesome-icon icon="plus" />
                      </button>
                      <button v-else type="button" class="btn btn-danger btn-sm" @click="removeExperience(index)">
                        <font-awesome-icon icon="minus" />
                      </button>
                    </div>
                  </template>
                </div>
              </template>
              <template v-else>
                <div class="col-md-12 col-sm-12" v-for="experience in dataUserExperiences" :key="experience.id">
                  <div class="row mb-2">
                    <div class="col-md-1">
                      <img src="https://play-lh.googleusercontent.com/DTzWtkxfnKwFO3ruybY1SKjJQnLYeuK3KmQmwV5OQ3dULr5iXxeEtzBLceultrKTIUTr" class="experience-img" alt="">
                    </div>
                    <div class="col-md-11">
                      <div class="h-100 mt-2">
                        <span class="text-muted mb-0 fw-bold me-1">{{ experience?.organization_e }},</span>
                        <span class="text-muted mb-0 me-1">{{ experience?.position_e }},</span>
                        <span class="text-muted mb-0 me-1">{{ experience?.started_at }}{{ experience?.finished_at ? ' - ' : '' }}</span>
                        <span class="text-muted mb-0 me-1" :class="{'fw-bold': !experience?.finished_at}">{{ experience?.finished_at ? experience?.finished_at : ' - по сей день' }}</span>
                        <span class="text-muted mb-0 me-1" v-if="experience?.started_at && experience?.finished_at">, {{ setExperienceMonths(experience?.started_at, experience?.finished_at) }} мес.</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'experience'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setExperienceFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('experience')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('experience')" />
                </template>
              </div>
            </div>
          </div>
          <template v-if="false">
            <hr>
            <div class="row">
              <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
                <p class="mb-0">Сфера деятельности</p>
              </div>
              <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
                <template v-if="showBlock == 'fieldOfActivities'">
                  <Multiselect
                    v-model="fieldOfActivityOptions"
                    mode="tags"
                    valueProp="id"
                    :options="fieldOfActivities"
                    :searchable="true"
                    label="name_f"
                    track-by="name_f"
                  />
                </template>
                <template v-else>
                  <span class="text-muted mb-0" v-for="(fieldOfActivity, index) in dataUserFieldOfActivities" :key="fieldOfActivity.id">
                    {{ fieldOfActivity?.field_of_activity.name_f }}{{ index + 1 == dataUserFieldOfActivities.length ? '' : ', ' }}
                  </span>
                </template>
              </div>
              <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
                <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                  <template v-if="showBlock == 'fieldOfActivities'">
                    <font-awesome-icon icon="check" class="fs-3 me-2" @click="setFieldOfActivityFunc" />
                    <font-awesome-icon icon="times" class="fs-3" @click="openBlock('fieldOfActivities')" />
                  </template>
                  <template v-else>
                    <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('fieldOfActivities')" />
                  </template>
                </div>
              </div>
            </div>
          </template>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Образование</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'educations'">
                <Multiselect
                  v-model="educationOptions"
                  mode="tags"
                  valueProp="id"
                  :options="educations"
                  :searchable="true"
                  label="name_e"
                  track-by="name_e"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(education, index) in dataUserEducations" :key="education.id">
                  {{ education?.education.name_e }}{{ index + 1 == dataUserEducations.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'educations'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setEducationFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('educations')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('educations')" />
                </template>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Профессиональные навыки</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'professionalSkills'">
                <Multiselect
                  v-model="professionalSkillOptions"
                  mode="tags"
                  valueProp="id"
                  :options="professionalSkills"
                  :searchable="true"
                  label="name_s"
                  track-by="name_s"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(skill, index) in dataUserProfessionalSkills" :key="skill.id">
                  {{ skill?.professional_skill.name_s }}{{ index + 1 == dataUserProfessionalSkills.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'professionalSkills'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setProfessionalSkillFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('professionalSkills')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('professionalSkills')" />
                </template>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Личные навыки</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'personalSkills'">
                <Multiselect
                  v-model="personalSkillOptions"
                  mode="tags"
                  valueProp="id"
                  :options="personalSkills"
                  :searchable="true"
                  label="name_p"
                  track-by="name_p"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(skill, index) in dataUserPersonalSkills" :key="skill.id">
                  {{ skill?.personal_skill.name_p }}{{ index + 1 == dataUserPersonalSkills.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'personalSkills'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setPersonalSkillFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('personalSkills')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('personalSkills')" />
                </template>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Знание языков</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'languageSkills'">
                <Multiselect
                  v-model="languageSkillOptions"
                  mode="tags"
                  valueProp="id"
                  :options="languageSkills"
                  :searchable="true"
                  label="name_l"
                  track-by="name_l"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(skill, index) in dataUserLanguageSkills" :key="skill.id">
                  {{ skill?.language_skill.name_l }}{{ index + 1 == dataUserLanguageSkills.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'languageSkills'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setLanguageSkillFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('languageSkills')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('languageSkills')" />
                </template>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Водительские права</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'driverLicenses'">
                <Multiselect
                  v-model="driverLicenseOptions"
                  mode="tags"
                  valueProp="id"
                  :options="driverLicenses"
                  :searchable="true"
                  label="name_d"
                  track-by="name_d"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(skill, index) in dataUserDriverLicenses" :key="skill.id">
                  {{ skill?.driver_license.name_d }}{{ index + 1 == dataUserDriverLicenses.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'driverLicenses'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setDriverLicenseFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('driverLicenses')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('driverLicenses')" />
                </template>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-3 col-lg-3 col-11 order-1 order-md-1 order-sm-1 mb-1">
              <p class="mb-0">Хобби и интересы</p>
            </div>
            <div class="col-sm-8 col-lg-8 col-12 order-3 order-md-2 order-sm-1 mb-1">
              <template v-if="showBlock == 'hobbies'">
                <Multiselect
                  v-model="hobbyOptions"
                  mode="tags"
                  valueProp="id"
                  :options="hobbies"
                  :searchable="true"
                  label="name_h"
                  track-by="name_h"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0" v-for="(skill, index) in dataUserHobbies" :key="skill.id">
                  {{ skill?.hobby.name_h }}{{ index + 1 == dataUserHobbies.length ? '' : ', ' }}
                </span>
              </template>
            </div>
            <div class="col-sm-1 col-lg-1 col-1 order-2 order-md-3 order-sm-1 mb-1 text-end">
              <div class="profile-save-icon h-100" v-if="$page.props.auth?.user?.role == 'WORKER'">
                <template v-if="showBlock == 'hobbies'">
                  <font-awesome-icon icon="check" class="fs-3 me-2" @click="setHobbyFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('hobbies')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('hobbies')" />
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
import {
  setPreferredPositions,
  setExperiences,
  setFieldOfActivities,
  setEducations,
  setProfessionalSkills,
  setPersonalSkills,
  setLanguageSkills,
  setDriverLicenses,
  setHobbies,
  setUserInfo
} from '@/Services/PersonalProfileInfo.js'
import { DatePicker } from 'v-calendar'
import moment from 'moment'
import { Required, Email, MinLength, Numeric, ImageExtension } from '@/Utils/validationMessages'
import useVuelidate from '@vuelidate/core'

const props = defineProps({
  title: String,
  positions: Object,
  userPositions: Object,
  fieldOfActivities: Object,
  userFieldOfActivities: Object,
  educations: Object,
  userEducations: Object,
  professionalSkills: Object,
  userProfessionalSkills: Object,
  personalSkills: Object,
  userPersonalSkills: Object,
  languageSkills: Object,
  userLanguageSkills: Object,
  driverLicenses: Object,
  userDriverLicenses: Object,
  hobbies: Object,
  userHobbies: Object,
  userExperiences: Object,
  userData: Object,
})

const showBlock = ref('')

const userProfileImg = ref(null)
const userProfileImgPreview = ref(null)

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

const dataUserPositions = ref(props?.userPositions)
const preferredPositions = ref([])
for(let p in props?.userPositions) {
  preferredPositions.value.push(props?.userPositions[p].position?.id)
}

const dataUserFieldOfActivities = ref(props?.userFieldOfActivities)
const fieldOfActivityOptions = ref([])
for(let p in props?.userFieldOfActivities) {
  fieldOfActivityOptions.value.push(props?.userFieldOfActivities[p].field_of_activity?.id)
}

const dataUserEducations = ref(props?.userEducations)
const educationOptions = ref([])
for(let p in props?.userEducations) {
  educationOptions.value.push(props?.userEducations[p].education?.id)
}

const dataUserProfessionalSkills = ref(props?.userProfessionalSkills)
const professionalSkillOptions = ref([])
for(let p in props?.userProfessionalSkills) {
  professionalSkillOptions.value.push(props?.userProfessionalSkills[p].professional_skill?.id)
}

const dataUserPersonalSkills = ref(props?.userPersonalSkills)
const personalSkillOptions = ref([])
for(let p in props?.userPersonalSkills) {
  personalSkillOptions.value.push(props?.userPersonalSkills[p].personal_skill?.id)
}

const dataUserLanguageSkills = ref(props?.userLanguageSkills)
const languageSkillOptions = ref([])
for(let p in props?.userLanguageSkills) {
  languageSkillOptions.value.push(props?.userLanguageSkills[p].language_skill?.id)
}

const dataUserDriverLicenses = ref(props?.userDriverLicenses)
const driverLicenseOptions = ref([])
for(let p in props?.userDriverLicenses) {
  driverLicenseOptions.value.push(props?.userDriverLicenses[p].driver_license?.id)
}

const dataUserHobbies = ref(props?.userHobbies)
const hobbyOptions = ref([])
for(let p in props?.userHobbies) {
  hobbyOptions.value.push(props?.userHobbies[p].hobby?.id)
}

const dataUserExperiences = ref(props?.userExperiences)
const dataUserExperienceDateError = ref([])
const experiences = ref([])

const openBlock = (type) => {
  if(showBlock.value == type) {
    showBlock.value = ''
  } else {
    showBlock.value = type
  }
};

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
};

const masks = {
  input: 'YYYY-MM-DD',
};

const experienceDatas = ref([
  {
    'organization': {
      'ru': '',
    },
    'position': {
      'ru': '',
    },
    'started_at': '',
    'finished_at': '',
    'is_active_work': false,
  }
])

if(props.userExperiences.length > 0) {
  setExperiencesInfo(props.userExperiences)
}

const addExperience = () => {
  experienceDatas.value.push({
    'organization': {
      'ru': '',
    },
    'position': {
      'ru': '',
    },
    'started_at': '',
    'finished_at': '',
    'is_active_work': false,
  })
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

const removeExperience = (i) => {
  experienceDatas.value = experienceDatas.value.filter(function(value, index, arr){
    return index != i;
  });
}

const setPreferredPositionFunc = async () => {
  const info = await setPreferredPositions(preferredPositions.value)
  dataUserPositions.value = info.userPositions
  showBlock.value = ''
}

const setExperienceFunc = async () => {
  if(dataUserExperienceDateError.value.length > 0) return false
  const info =  await setExperiences(experienceDatas.value)
  dataUserExperiences.value = info.userExperiences
  showBlock.value = ''
}

const setFieldOfActivityFunc = async () => {
  const info = await setFieldOfActivities(fieldOfActivityOptions.value)
  dataUserFieldOfActivities.value = info.userFieldOfActivities
  showBlock.value = ''
}

const setEducationFunc = async () => {
  const info = await setEducations(educationOptions.value)
  dataUserEducations.value = info.userEducations
  showBlock.value = ''
}

const setProfessionalSkillFunc = async () => {
  const info = await setProfessionalSkills(professionalSkillOptions.value)
  dataUserProfessionalSkills.value = info.userProfessionalSkills
  showBlock.value = ''
}

const setPersonalSkillFunc = async () => {
  const info = await setPersonalSkills(personalSkillOptions.value)
  dataUserPersonalSkills.value = info.userPersonalSkills
  showBlock.value = ''
}

const setLanguageSkillFunc = async () => {
  const info = await setLanguageSkills(languageSkillOptions.value)
  dataUserLanguageSkills.value = info.userLanguageSkills
  showBlock.value = ''
}

const setDriverLicenseFunc = async () => {
  const info = await setDriverLicenses(driverLicenseOptions.value)
  dataUserDriverLicenses.value = info.userDriverLicenses
  showBlock.value = ''
}

const setHobbyFunc = async () => {
  const info = await setHobbies(hobbyOptions.value)
  dataUserHobbies.value = info.userHobbies
  showBlock.value = ''
}

function setExperiencesInfo(data) {
  experienceDatas.value = []
  for(let e in data) {
    experienceDatas.value.push({
      'organization': {
        'ru': data[e].organization_e,
      },
      'position': {
        'ru': data[e].position_e,
      },
      'started_at': data[e].started_at,
      'finished_at': data[e].finished_at,
      'is_active_work': data[e].is_active_work ? true : false,
    })
  }
}

const isActiveWorkFunc = (e, idx) => {
  experienceDatas.value[idx].finished_at = ''
}

const setExperienceMonths = (startDate, endDate) => {
  startDate = moment(startDate, 'YYYY-MM-DD');
  endDate = moment(endDate, 'YYYY-MM-DD');
  return endDate.diff(startDate, 'months')
}

const setExperienceDays = (startDate, endDate, index) => {
  startDate = moment(startDate, 'YYYY-MM-DD');
  endDate = moment(endDate, 'YYYY-MM-DD');
  const days = endDate.diff(startDate, 'day')
  if(days <= 0)
    dataUserExperienceDateError.value.push(index)
  else
    dataUserExperienceDateError.value.splice(dataUserExperienceDateError.value.indexOf(index), 1);

  return days
}

const setUserInfoFunc = async () => {
  u$.value.$touch();
  if (u$._value.$invalid) return false;

  if((userData.value.password || userData.value.password_confirmation) && userData.value.password != userData.value.password_confirmation) {
    return false
  }

  const row = await setUserInfo(userData.value, 'set_user_info')
  setUserProfileNames(row)
}

const openUploadFile = (type) => {
  document.getElementById(type).click()
}

const updatePhotoPreview = (e, type) => {
  let  file = userProfileImg.value.files[0]

  const reader = new FileReader();
  reader.onload = (e) => {
    userProfileImgPreview.value = e.target.result
    userData.value.img = e.target.result
    userData.value.imgFile = file
    e.target.value = "";
  }

  reader.readAsDataURL(file);
}
</script>
