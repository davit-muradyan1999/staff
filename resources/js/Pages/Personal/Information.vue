<template>
  <div class="row g-3">
    <div class="col-lg-12">
      <div class="card mb-3">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">{{ $t("general.citizenship").toUpperCase() }}</p>
            </div>
            <div class="col-sm-8">

              <template v-if="showBlock == 'citizenship'">
                <label class="radio-inline me-4" v-for="citizenship in citizenships" :key="citizenship.id">
                  <input type="radio" name="optradio" @change="changeCitizenship" :value="citizenship.id" v-model="citizenshipData.citizenship_id" class="form-check-input me-2">{{ citizenship.name_c }}
                </label>

              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ citizenshipData?.citizenship_name }}</span>
              </template>

            </div>
            <div class="col-sm-1 text-end">
              <div class="profile-save-icon h-100" v-if="user?.status_latest?.status != 'CONFIRMED'">
                <template v-if="showBlock == 'citizenship'">
                  <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setCitizenshipFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('citizenship')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('citizenship')" />
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
              <p class="mb-0">
                <template v-if="citizenshipData.citizenship_id == 1">ПАСПОРТ</template>
                <template v-else-if="citizenshipData.citizenship_id == 2">Паспорт (заграничный или внутренний)</template>
                <template v-else-if="citizenshipData.citizenship_id == 3">Паспорт (заграничный или внутренний)</template>
                <template v-else-if="citizenshipData.citizenship_id == 4">Национальный паспорт</template>
                <template v-else>ПАСПОРТ</template>
              </p>
            </div>
            <div class="col-sm-8">


            </div>
            <div class="col-sm-1 text-end">
              <div class="profile-save-icon h-100" v-if="user?.status_latest?.status != 'CONFIRMED'">
                <template v-if="showBlock == 'passport'">
                  <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setPassportFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('passport')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('passport')" />
                </template>
              </div>
            </div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Паспорт (Файл 1)</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input
                  type="file"
                  class="form-control d-none"
                  id="passport_file_1"
                  ref="icon"
                  @change="updateFile($event, 'passport_file_1')"
                >
                <div class="row">
                  <div class="col-md-11">
                    <input
                      type="text"
                      @click="openUploadFiles('passport_file_1')"
                      :value="passportData?.passport_file_1 ? passportData?.passport_file_1_name : 'Выбрать файл'"
                      readonly
                      class="form-control cursor-pointer"
                    >
                  </div>
                  <div class="col-md-1 p-2">
                    <template v-if="passportData?.passport_file_1_file">
                      <a target="_blank" :href="`/storage/${passportData?.passport_file_1_file}`" :class="{'opacity-50': passportData.passport_file_1_remove}">
                        <font-awesome-icon icon="file-lines" class="list-info" />
                      </a>
                      <font-awesome-icon :icon="passportData.passport_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="passportData.passport_file_1_remove = !passportData.passport_file_1_remove" />
                    </template>
                  </div>
                </div>
                <template v-if="passportData?.passport_file_1">
                  <span class="text-danger cursor-pointer" @click="passportData.passport_file_1 = null, passportData.passport_file_1_name = ''">Удалить</span>
                </template>
              </template>
              <template v-else>
                <template v-if="passportData?.passport_file_1_file">
                  <a target="_blank" :href="`/storage/${passportData?.passport_file_1_file}`">
                    <font-awesome-icon icon="file-lines" class="list-info" />
                  </a>
                </template>
                <template v-else>

                </template>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>

          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Паспорт (Файл 2)</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input
                  type="file"
                  class="form-control d-none"
                  id="passport_file_2"
                  ref="icon"
                  @change="updateFile($event, 'passport_file_2')"
                >
                <div class="row">
                  <div class="col-md-11">
                    <input
                      type="text"
                      @click="openUploadFiles('passport_file_2')"
                      :value="passportData?.passport_file_2 ? passportData?.passport_file_2_name : 'Выбрать файл'"
                      readonly
                      class="form-control cursor-pointer"
                    >
                  </div>
                  <div class="col-md-1 p-2">
                    <template v-if="passportData?.passport_file_2_file">
                      <a target="_blank" :href="`/storage/${passportData?.passport_file_2_file}`" :class="{'opacity-50': passportData.passport_file_2_remove}">
                        <font-awesome-icon icon="file-lines" class="list-info" />
                      </a>
                      <font-awesome-icon :icon="passportData.passport_file_2_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="passportData.passport_file_2_remove = !passportData.passport_file_2_remove" />
                    </template>
                  </div>
                </div>
                <template v-if="passportData?.passport_file_2">
                  <span class="text-danger cursor-pointer" @click="passportData.passport_file_2 = null, passportData.passport_file_2_name = ''">Удалить</span>
                </template>
              </template>
              <template v-else>
                <template v-if="passportData?.passport_file_2_file">
                  <a target="_blank" :href="`/storage/${passportData?.passport_file_2_file}`">
                    <font-awesome-icon icon="file-lines" class="list-info" />
                  </a>
                </template>
                <template v-else>

                </template>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>

          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Паспорт (Файл 3)</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input
                  type="file"
                  class="form-control d-none"
                  id="passport_file_3"
                  ref="icon"
                  @change="updateFile($event, 'passport_file_3')"
                >
                <div class="row">
                  <div class="col-md-11">
                    <input
                      type="text"
                      @click="openUploadFiles('passport_file_3')"
                      :value="passportData?.passport_file_3 ? passportData?.passport_file_3_name : 'Выбрать файл'"
                      readonly
                      class="form-control cursor-pointer"
                    >
                  </div>
                  <div class="col-md-1 p-2">
                    <template v-if="passportData?.passport_file_3_file">
                      <a target="_blank" :href="`/storage/${passportData?.passport_file_3_file}`" :class="{'opacity-50': passportData.passport_file_3_remove}">
                        <font-awesome-icon icon="file-lines" class="list-info" />
                      </a>
                      <font-awesome-icon :icon="passportData.passport_file_3_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="passportData.passport_file_3_remove = !passportData.passport_file_3_remove" />
                    </template>
                  </div>
                </div>
                <template v-if="passportData?.passport_file_3">
                  <span class="text-danger cursor-pointer" @click="passportData.passport_file_3 = null, passportData.passport_file_3_name = ''">Удалить</span>
                </template>
              </template>
              <template v-else>
                <template v-if="passportData?.passport_file_3_file">
                  <a target="_blank" :href="`/storage/${passportData?.passport_file_3_file}`">
                    <font-awesome-icon icon="file-lines" class="list-info" />
                  </a>
                </template>
                <template v-else>

                </template>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>

          <template v-if="citizenshipData.citizenship_id != 1">

            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Перевод паспорта (Файл 1)</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'passport'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="passport_translation_file_1"
                    ref="icon"
                    @change="updateFile($event, 'passport_translation_file_1')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('passport_translation_file_1')"
                        :value="passportData?.passport_translation_file_1 ? passportData?.passport_translation_file_1_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="passportData?.passport_translation_file_1_file">
                        <a target="_blank" :href="`/storage/${passportData?.passport_translation_file_1_file}`" :class="{'opacity-50': passportData.passport_translation_file_1_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="passportData.passport_translation_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="passportData.passport_translation_file_1_remove = !passportData.passport_translation_file_1_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="passportData?.passport_translation_file_1">
                    <span class="text-danger cursor-pointer" @click="passportData.passport_translation_file_1 = null, passportData.passport_translation_file_1_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="passportData?.passport_translation_file_1_file">
                    <a target="_blank" :href="`/storage/${passportData?.passport_translation_file_1_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>

            <hr>

            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Перевод паспорта (Файл 2)</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'passport'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="passport_translation_file_2"
                    ref="icon"
                    @change="updateFile($event, 'passport_translation_file_2')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('passport_translation_file_2')"
                        :value="passportData?.passport_translation_file_2 ? passportData?.passport_translation_file_2_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="passportData?.passport_translation_file_2_file">
                        <a target="_blank" :href="`/storage/${passportData?.passport_translation_file_2_file}`" :class="{'opacity-50': passportData.passport_translation_file_2_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="passportData.passport_translation_file_2_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="passportData.passport_translation_file_2_remove = !passportData.passport_translation_file_2_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="passportData?.passport_translation_file_2">
                    <span class="text-danger cursor-pointer" @click="passportData.passport_translation_file_2 = null, passportData.passport_translation_file_2_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="passportData?.passport_translation_file_2_file">
                    <a target="_blank" :href="`/storage/${passportData?.passport_translation_file_2_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>

            <hr>

            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Перевод паспорта (Файл 3)</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'passport'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="passport_translation_file_3"
                    ref="icon"
                    @change="updateFile($event, 'passport_translation_file_3')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('passport_translation_file_3')"
                        :value="passportData?.passport_translation_file_3 ? passportData?.passport_translation_file_3_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="passportData?.passport_translation_file_3_file">
                        <a target="_blank" :href="`/storage/${passportData?.passport_translation_file_3_file}`" :class="{'opacity-50': passportData.passport_translation_file_3_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="passportData.passport_translation_file_3_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="passportData.passport_translation_file_3_remove = !passportData.passport_translation_file_3_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="passportData?.passport_translation_file_3">
                    <span class="text-danger cursor-pointer" @click="passportData.passport_translation_file_3 = null, passportData.passport_translation_file_3_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="passportData?.passport_translation_file_3_file">
                    <a target="_blank" :href="`/storage/${passportData?.passport_translation_file_3_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>

            <hr>
          </template>


          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Имя</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.first_name['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.first_name?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Фамилия</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.last_name['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.last_name?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Отчество</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.surname['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.surname?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Дата рождения</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <DatePicker v-model="passportData.birthday" :model-config="pickerConfig" :masks="masks" mode="date" :popover="{ visibility: 'focus' }" locale="ru">
                  <template v-slot="{ inputValue, inputEvents }">
                    <input
                      class="form-control check-list-dt"
                      :value="inputValue"
                      v-on="inputEvents"
                    />
                  </template>
                </DatePicker>
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.birthday }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Место рождения</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.place_of_birth['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.place_of_birth?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Пол</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <Multiselect
                  v-model="passportData.gender_id"
                  valueProp="id"
                  :options="genders"
                  :searchable="true"
                  label="name_g"
                  track-by="name_g"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.gender_name }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Серия и номер</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.series_and_number">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.series_and_number }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Кем выдан</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.issued_by['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.issued_by?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Дата выдачи</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <DatePicker v-model="passportData.date_of_issue" :model-config="pickerConfig" :masks="masks" mode="date" :popover="{ visibility: 'focus' }" locale="ru">
                  <template v-slot="{ inputValue, inputEvents }">
                    <input
                      class="form-control check-list-dt"
                      :value="inputValue"
                      v-on="inputEvents"
                    />
                  </template>
                </DatePicker>
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.date_of_issue }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Срок действия</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <DatePicker v-model="passportData.validity" :model-config="pickerConfig" :masks="masks" mode="date" :popover="{ visibility: 'focus' }" locale="ru">
                  <template v-slot="{ inputValue, inputEvents }">
                    <input
                      class="form-control check-list-dt"
                      :value="inputValue"
                      v-on="inputEvents"
                    />
                  </template>
                </DatePicker>
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.validity }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Адрес места жительства</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'passport'">
                <input type="text" class="form-control" v-model="passportData.residence_address['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ passportData?.residence_address?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

        </div>
      </div>


      <div class="card mb-3">
        <div class="card-body">
          <template v-if="citizenshipData.citizenship_id == 1">
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Страховое свидетельство обязательного пенсионного страхования</p>
              </div>
              <div class="col-sm-8">
                <div class="row">
                  <div class="col-md-6">
                    <template v-if="showBlock == 'files'">
                      <input type="text" class="form-control" v-model="filesData.insurance_certificate['ru']">
                    </template>
                    <template v-else>
                      <span class="text-muted mb-0">{{ filesData?.insurance_certificate?.ru }}</span>
                    </template>
                  </div>
                  <div class="col-md-6">
                    <template v-if="showBlock == 'files'">
                      <input
                        type="file"
                        class="form-control d-none"
                        id="insurance_certificate_file"
                        ref="icon"
                        @change="updateFile($event, 'insurance_certificate_file')"
                      >
                      <div class="row">
                        <div class="col-md-10">
                          <input
                            type="text"
                            @click="openUploadFiles('insurance_certificate_file')"
                            :value="filesData?.insurance_certificate_file ? filesData?.insurance_certificate_file_name : 'Выбрать файл'"
                            readonly
                            class="form-control cursor-pointer"
                          >
                        </div>
                        <div class="col-md-2 p-2">
                          <template v-if="filesData?.insurance_certificate_file_file">
                            <a target="_blank" :href="`/storage/${filesData?.insurance_certificate_file_file}`" :class="{'opacity-50': filesData.insurance_certificate_file_remove}">
                              <font-awesome-icon icon="file-lines" class="list-info" />
                            </a>
                            <font-awesome-icon :icon="filesData.insurance_certificate_file_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.insurance_certificate_file_remove = !filesData.insurance_certificate_file_remove" />
                          </template>
                        </div>
                      </div>
                      <template v-if="filesData?.insurance_certificate_file">
                        <span class="text-danger cursor-pointer" @click="filesData.insurance_certificate_file = null, filesData.insurance_certificate_file = ''">Удалить</span>
                      </template>
                    </template>
                    <template v-else>
                      <span class="text-muted mb-0">
                        <template v-if="filesData?.insurance_certificate_file_file">
                          <a target="_blank" :href="`/storage/${filesData?.insurance_certificate_file_file}`">
                            <font-awesome-icon icon="file-lines" class="list-info" />
                          </a>
                        </template>
                        <template v-else>

                        </template>
                      </span>
                    </template>
                  </div>
                </div>
              </div>
              <div class="col-sm-1 text-end">
                <div class="profile-save-icon h-100" v-if="user?.status_latest?.status != 'CONFIRMED'">
                  <template v-if="showBlock == 'files'">
                    <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setFilesFunc" />
                    <font-awesome-icon icon="times" class="fs-3" @click="openBlock('files')" />
                  </template>
                  <template v-else>
                    <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('files')" />
                  </template>
                </div>
              </div>
            </div>
            <hr>
          </template>


          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Группа инвалидности</p>
            </div>
            <div class="col-sm-8">

              <div class="row">
                <div class="col-md-6">
                  <template v-if="showBlock == 'files'">
                    <Multiselect
                      v-model="filesData.disability_group_id"
                      valueProp="id"
                      :options="disabilityGroups"
                      :searchable="true"
                      label="name_d"
                      track-by="name_d"
                    />
                  </template>
                  <template v-else>
                    <span class="text-muted mb-0">{{ filesData?.disability_group_name }}</span>
                  </template>
                </div>
                <div class="col-md-6">
                  <template v-if="showBlock == 'files'">
                    <input
                      type="file"
                      class="form-control d-none"
                      id="disability_group_file"
                      ref="icon"
                      @change="updateFile($event, 'disability_group_file')"
                    >
                    <div class="row">
                      <div class="col-md-10">
                        <input
                          type="text"
                          @click="openUploadFiles('disability_group_file')"
                          :value="filesData?.disability_group_file ? filesData?.disability_group_file_name : 'Выбрать файл'"
                          readonly
                          class="form-control cursor-pointer"
                        >
                      </div>
                      <div class="col-md-2 p-2">
                        <template v-if="filesData?.disability_group_file_file">
                          <a target="_blank" :href="`/storage/${filesData?.disability_group_file_file}`" :class="{'opacity-50': filesData.disability_group_file_remove}">
                            <font-awesome-icon icon="file-lines" class="list-info" />
                          </a>
                          <font-awesome-icon :icon="filesData.disability_group_file_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.disability_group_file_remove = !filesData.disability_group_file_remove" />
                        </template>
                      </div>
                    </div>
                    <template v-if="filesData?.disability_group_file">
                      <span class="text-danger cursor-pointer" @click="filesData.disability_group_file = null, filesData.disability_group_file = ''">Удалить</span>
                    </template>
                  </template>
                  <template v-else>
                    <span class="text-muted mb-0">
                      <template v-if="filesData?.disability_group_file_file">
                        <a target="_blank" :href="`/storage/${filesData?.disability_group_file_file}`">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                      </template>
                      <template v-else>

                      </template>
                    </span>
                  </template>

                </div>
              </div>

            </div>
            <div class="col-sm-1 text-end">
              <div class="profile-save-icon h-100" v-if="user?.status_latest?.status != 'CONFIRMED' && citizenshipData.citizenship_id != 1">
                  <template v-if="showBlock == 'files'">
                    <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setFilesFunc" />
                    <font-awesome-icon icon="times" class="fs-3" @click="openBlock('files')" />
                  </template>
                  <template v-else>
                    <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('files')" />
                  </template>
                </div>
            </div>
          </div>

          <template v-if="citizenshipData.citizenship_id == 1">
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Медкнижка</p>
              </div>
              <div class="col-sm-8">
                <div class="row">
                  <div class="col-md-6">
                    <template v-if="showBlock == 'files'">
                      <input type="text" class="form-control" v-model="filesData.medical_book['ru']">
                    </template>
                    <template v-else>
                      <span class="text-muted mb-0">{{ filesData?.medical_book?.ru }}</span>
                    </template>
                  </div>
                  <div class="col-md-6">
                    <template v-if="showBlock == 'files'">
                      <input
                        type="file"
                        class="form-control d-none"
                        id="medical_book_file"
                        ref="icon"
                        @change="updateFile($event, 'medical_book_file')"
                      >
                      <div class="row">
                        <div class="col-md-10">
                          <input
                            type="text"
                            @click="openUploadFiles('medical_book_file')"
                            :value="filesData?.medical_book_file ? filesData?.medical_book_file_name : 'Выбрать файл'"
                            readonly
                            class="form-control cursor-pointer"
                          >
                        </div>
                        <div class="col-md-2 p-2">
                          <template v-if="filesData?.medical_book_file_file">
                            <a target="_blank" :href="`/storage/${filesData?.medical_book_file_file}`" :class="{'opacity-50': filesData.medical_book_file_remove}">
                              <font-awesome-icon icon="file-lines" class="list-info" />
                            </a>
                            <font-awesome-icon :icon="filesData.medical_book_file_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.medical_book_file_remove = !filesData.medical_book_file_remove" />
                          </template>
                        </div>
                      </div>
                      <template v-if="filesData?.medical_book_file">
                        <span class="text-danger cursor-pointer" @click="filesData.medical_book_file = null, filesData.medical_book_file = ''">Удалить</span>
                      </template>
                    </template>
                    <template v-else>
                      <span class="text-muted mb-0">
                        <template v-if="filesData?.medical_book_file_file">
                          <a target="_blank" :href="`/storage/${filesData?.medical_book_file_file}`">
                            <font-awesome-icon icon="file-lines" class="list-info" />
                          </a>
                        </template>
                        <template v-else>

                        </template>
                      </span>
                    </template>
                  </div>
                </div>
              </div>
              <div class="col-sm-1 text-end">

              </div>
            </div>
          </template>

          <template v-if="citizenshipData.citizenship_id == 3 || citizenshipData.citizenship_id == 4">

            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Миграционная карта</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'files'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="migration_card_file_1"
                    ref="icon"
                    @change="updateFile($event, 'migration_card_file_1')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('migration_card_file_1')"
                        :value="filesData?.migration_card_file_1 ? filesData?.migration_card_file_1_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="filesData?.migration_card_file_1_file">
                        <a target="_blank" :href="`/storage/${filesData?.migration_card_file_1_file}`" :class="{'opacity-50': filesData.migration_card_file_1_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="filesData.migration_card_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.migration_card_file_1_remove = !filesData.migration_card_file_1_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="filesData?.migration_card_file_1">
                    <span class="text-danger cursor-pointer" @click="filesData.migration_card_file_1 = null, filesData.migration_card_file_1_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="filesData?.migration_card_file_1_file">
                    <a target="_blank" :href="`/storage/${filesData?.migration_card_file_1_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>

            <template v-if="citizenshipData.citizenship_id == 3">
              <hr>
              <div class="row">
                <div class="col-sm-3">
                  <p class="mb-0">Действующий миграционный учет (если с даты въезда иностранца прошло 30 суток)</p>
                </div>
                <div class="col-sm-8">
                  <template v-if="showBlock == 'files'">
                    <input
                      type="file"
                      class="form-control d-none"
                      id="migration_account_file_1"
                      ref="icon"
                      @change="updateFile($event, 'migration_account_file_1')"
                    >
                    <div class="row">
                      <div class="col-md-11">
                        <input
                          type="text"
                          @click="openUploadFiles('migration_account_file_1')"
                          :value="filesData?.migration_account_file_1 ? filesData?.migration_account_file_1_name : 'Выбрать файл'"
                          readonly
                          class="form-control cursor-pointer"
                        >
                      </div>
                      <div class="col-md-1 p-2">
                        <template v-if="filesData?.migration_account_file_1_file">
                          <a target="_blank" :href="`/storage/${filesData?.migration_account_file_1_file}`" :class="{'opacity-50': filesData.migration_account_file_1_remove}">
                            <font-awesome-icon icon="file-lines" class="list-info" />
                          </a>
                          <font-awesome-icon :icon="filesData.migration_account_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.migration_account_file_1_remove = !filesData.migration_account_file_1_remove" />
                        </template>
                      </div>
                    </div>
                    <template v-if="filesData?.migration_account_file_1">
                      <span class="text-danger cursor-pointer" @click="filesData.migration_account_file_1 = null, filesData.migration_account_file_1_name = ''">Удалить</span>
                    </template>
                  </template>
                  <template v-else>
                    <template v-if="filesData?.migration_account_file_1_file">
                      <a target="_blank" :href="`/storage/${filesData?.migration_account_file_1_file}`">
                        <font-awesome-icon icon="file-lines" class="list-info" />
                      </a>
                    </template>
                    <template v-else>

                    </template>
                  </template>
                </div>
                <div class="col-sm-1 text-end"></div>
              </div>
            </template>

          </template>

          <template v-if="citizenshipData.citizenship_id == 4">

            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">РВП или РВПО (для временно проживающих иностранцев)</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'files'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="rvp_file_1"
                    ref="icon"
                    @change="updateFile($event, 'rvp_file_1')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('rvp_file_1')"
                        :value="filesData?.rvp_file_1 ? filesData?.rvp_file_1_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="filesData?.rvp_file_1_file">
                        <a target="_blank" :href="`/storage/${filesData?.rvp_file_1_file}`" :class="{'opacity-50': filesData.rvp_file_1_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="filesData.rvp_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.rvp_file_1_remove = !filesData.rvp_file_1_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="filesData?.rvp_file_1">
                    <span class="text-danger cursor-pointer" @click="filesData.rvp_file_1 = null, filesData.rvp_file_1_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="filesData?.rvp_file_1_file">
                    <a target="_blank" :href="`/storage/${filesData?.rvp_file_1_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>

            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">ВНЖ (для постоянно проживающих иностранцев)</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'files'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="vnj_file_1"
                    ref="icon"
                    @change="updateFile($event, 'vnj_file_1')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('vnj_file_1')"
                        :value="filesData?.vnj_file_1 ? filesData?.vnj_file_1_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="filesData?.vnj_file_1_file">
                        <a target="_blank" :href="`/storage/${filesData?.vnj_file_1_file}`" :class="{'opacity-50': filesData.vnj_file_1_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="filesData.vnj_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.vnj_file_1_remove = !filesData.vnj_file_1_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="filesData?.vnj_file_1">
                    <span class="text-danger cursor-pointer" @click="filesData.vnj_file_1 = null, filesData.vnj_file_1_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="filesData?.vnj_file_1_file">
                    <a target="_blank" :href="`/storage/${filesData?.vnj_file_1_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>

            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Патент и чек об оплате патента</p>
              </div>
              <div class="col-sm-8">
                <template v-if="showBlock == 'files'">
                  <input
                    type="file"
                    class="form-control d-none"
                    id="payment_check_file_1"
                    ref="icon"
                    @change="updateFile($event, 'payment_check_file_1')"
                  >
                  <div class="row">
                    <div class="col-md-11">
                      <input
                        type="text"
                        @click="openUploadFiles('payment_check_file_1')"
                        :value="filesData?.payment_check_file_1 ? filesData?.payment_check_file_1_name : 'Выбрать файл'"
                        readonly
                        class="form-control cursor-pointer"
                      >
                    </div>
                    <div class="col-md-1 p-2">
                      <template v-if="filesData?.payment_check_file_1_file">
                        <a target="_blank" :href="`/storage/${filesData?.payment_check_file_1_file}`" :class="{'opacity-50': filesData.payment_check_file_1_remove}">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                        <font-awesome-icon :icon="filesData.payment_check_file_1_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.payment_check_file_1_remove = !filesData.payment_check_file_1_remove" />
                      </template>
                    </div>
                  </div>
                  <template v-if="filesData?.payment_check_file_1">
                    <span class="text-danger cursor-pointer" @click="filesData.payment_check_file_1 = null, filesData.payment_check_file_1_name = ''">Удалить</span>
                  </template>
                </template>
                <template v-else>
                  <template v-if="filesData?.payment_check_file_1_file">
                    <a target="_blank" :href="`/storage/${filesData?.payment_check_file_1_file}`">
                      <font-awesome-icon icon="file-lines" class="list-info" />
                    </a>
                  </template>
                  <template v-else>

                  </template>
                </template>
              </div>
              <div class="col-sm-1 text-end"></div>
            </div>



          </template>

          <hr>

          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">СНИЛС</p>
            </div>
            <div class="col-sm-8">
              <div class="row">
                <div class="col-md-6">
                  <template v-if="showBlock == 'files'">
                    <input type="text" class="form-control" v-model="filesData.snils['ru']">
                  </template>
                  <template v-else>
                    <span class="text-muted mb-0">{{ filesData?.snils?.ru }}</span>
                  </template>
                </div>
                <div class="col-md-6">
                  <template v-if="showBlock == 'files'">
                    <input
                      type="file"
                      class="form-control d-none"
                      id="snils_file"
                      ref="icon"
                      @change="updateFile($event, 'snils_file')"
                    >
                    <div class="row">
                      <div class="col-md-10">
                        <input
                          type="text"
                          @click="openUploadFiles('snils_file')"
                          :value="filesData?.snils_file ? filesData?.snils_file_name : 'Выбрать файл'"
                          readonly
                          class="form-control cursor-pointer"
                        >
                      </div>
                      <div class="col-md-2 p-2">
                        <template v-if="filesData?.snils_file_file">
                          <a target="_blank" :href="`/storage/${filesData?.snils_file_file}`" :class="{'opacity-50': filesData.snils_file_remove}">
                            <font-awesome-icon icon="file-lines" class="list-info" />
                          </a>
                          <font-awesome-icon :icon="filesData.snils_file_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.snils_file_remove = !filesData.snils_file_remove" />
                        </template>
                      </div>
                    </div>
                    <template v-if="filesData?.snils_file">
                      <span class="text-danger cursor-pointer" @click="filesData.snils_file = null, filesData.snils_file = ''">Удалить</span>
                    </template>
                  </template>
                  <template v-else>
                    <span class="text-muted mb-0">
                      <template v-if="filesData?.snils_file_file">
                        <a target="_blank" :href="`/storage/${filesData?.snils_file_file}`">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                      </template>
                      <template v-else>

                      </template>
                    </span>
                  </template>
                </div>
              </div>
            </div>
            <div class="col-sm-1 text-end">

            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">ИНН</p>
            </div>
            <div class="col-sm-8">
              <div class="row">
                <div class="col-md-6">
                  <template v-if="showBlock == 'files'">
                    <input type="text" class="form-control" v-model="filesData.inn['ru']">
                  </template>
                  <template v-else>
                    <span class="text-muted mb-0">{{ filesData?.inn?.ru }}</span>
                  </template>
                </div>
                <div class="col-md-6">
                  <template v-if="showBlock == 'files'">
                    <input
                      type="file"
                      class="form-control d-none"
                      id="inn_file"
                      ref="icon"
                      @change="updateFile($event, 'inn_file')"
                    >
                    <div class="row">
                      <div class="col-md-10">
                        <input
                          type="text"
                          @click="openUploadFiles('inn_file')"
                          :value="filesData?.inn_file ? filesData?.inn_file_name : 'Выбрать файл'"
                          readonly
                          class="form-control cursor-pointer"
                        >
                      </div>
                      <div class="col-md-2 p-2">
                        <template v-if="filesData?.inn_file_file">
                          <a target="_blank" :href="`/storage/${filesData?.inn_file_file}`" :class="{'opacity-50': filesData.inn_file_remove}">
                            <font-awesome-icon icon="file-lines" class="list-info" />
                          </a>
                          <font-awesome-icon :icon="filesData.inn_file_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="filesData.inn_file_remove = !filesData.inn_file_remove" />
                        </template>
                      </div>
                    </div>
                    <template v-if="filesData?.inn_file">
                      <span class="text-danger cursor-pointer" @click="filesData.inn_file = null, filesData.inn_file = ''">Удалить</span>
                    </template>
                  </template>
                  <template v-else>
                    <span class="text-muted mb-0">
                      <template v-if="filesData?.inn_file_file">
                        <a target="_blank" :href="`/storage/${filesData?.inn_file_file}`">
                          <font-awesome-icon icon="file-lines" class="list-info" />
                        </a>
                      </template>
                      <template v-else>

                      </template>
                    </span>
                  </template>
                </div>
              </div>
            </div>
            <div class="col-sm-1 text-end">

            </div>
          </div>

        </div>
      </div>


      <div class="card mb-3">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Реквизиты банковской карты</p>
            </div>
            <div class="col-sm-8">


            </div>
            <div class="col-sm-1 text-end">
              <div class="profile-save-icon h-100" v-if="user?.status_latest?.status != 'CONFIRMED'">
                <template v-if="showBlock == 'card'">
                  <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setCardFunc" />
                  <font-awesome-icon icon="times" class="fs-3" @click="openBlock('card')" />
                </template>
                <template v-else>
                  <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('card')" />
                </template>
              </div>
            </div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Валюта получаемого перевода</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <Multiselect
                  v-model="cardData.currency_id"
                  valueProp="id"
                  :options="currencies"
                  :searchable="true"
                  label="name"
                  track-by="name"
                  :disabled="true"
                />
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ $t("general.rub") }}.</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Получатель</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.recipient['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.recipient?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Номер счёта</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.account_number">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.account_number }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Банк получателя</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.recipient_bank['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.recipient_bank?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">БИК</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.bik['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.bik?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">Корр. счёт</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.correspondent_account['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.correspondent_account?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">ИНН</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" disabled class="form-control" v-model="filesData.inn['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ filesData?.inn?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">КПП</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.kpp['ru']">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.kpp?.ru }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

          <hr>
          <div class="row">
            <div class="col-sm-3">
              <p class="mb-0">SWIFT-код</p>
            </div>
            <div class="col-sm-8">
              <template v-if="showBlock == 'card'">
                <input type="text" class="form-control" v-model="cardData.swift_code">
              </template>
              <template v-else>
                <span class="text-muted mb-0">{{ cardData?.swift_code }}</span>
              </template>
            </div>
            <div class="col-sm-1 text-end"></div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import Layout from '@/Layouts/Personal/AppLayout.vue'
import useVuelidate from '@vuelidate/core'
import { ref, defineEmits } from 'vue'
import { Required, MaxLength } from '@/Utils/validationMessages'
import { useForm } from '@inertiajs/vue3'
import { DatePicker } from 'v-calendar'
import {
  setCitizenship,
  setPassport,
  setFiles,
  setBankCard
} from '@/Services/PersonalProfileInfo.js'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  user: Object,
  cities: Object,
  genders: Object,
  disabilityGroups: Object,
  citizenships: Object,
})

const form = useForm({
  name: props.user?.name,
  birthday: props.user?.birthday,
  citizenship_id: props.user?.citizenship_id,
  gender_id: props.user?.gender_id,
  registration_address: props.user?.registration_address,
  medical_card_number: props.user?.medical_card_number,
  address_of_residence: props.user?.address_of_residence,
  phone: props.user?.phone,
})

const rules = {
  name: { Required, MaxLength: MaxLength(255) },
  birthday: { Required },
  citizenship_id: { Required },
  gender_id: { Required },
  registration_address: { Required, MaxLength: MaxLength(255) },
  medical_card_number: { MaxLength: MaxLength(255) },
  address_of_residence: { MaxLength: MaxLength(255) },
  phone: { MaxLength: MaxLength(255) },
}

const v$ = useVuelidate(rules, form)

const save = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  form.post(route('set_information'), form)
}

const citizenshipData = ref({
  user_id: props.user.id,
  citizenship_id: '',
  citizenship_name: '',
})

const passportData = ref({
  user_id: props.user.id,

  passport_file_1: '',
  passport_file_1_remove: false,
  passport_file_1_name: '',
  passport_file_1_file: '',

  passport_file_2: '',
  passport_file_2_remove: false,
  passport_file_2_name: '',
  passport_file_2_file: '',

  passport_file_3: '',
  passport_file_3_remove: false,
  passport_file_3_name: '',
  passport_file_3_file: '',

  passport_translation_file_1: '',
  passport_translation_file_1_remove: false,
  passport_translation_file_1_name: '',
  passport_translation_file_1_file: '',

  passport_translation_file_2: '',
  passport_translation_file_2_remove: false,
  passport_translation_file_2_name: '',
  passport_translation_file_2_file: '',

  passport_translation_file_3: '',
  passport_translation_file_3_remove: false,
  passport_translation_file_3_name: '',
  passport_translation_file_3_file: '',

  first_name: {'ru': ''},
  last_name: {'ru': ''},
  surname: {'ru': ''},
  birthday: null,
  place_of_birth: {'ru': ''},
  gender_id: null,
  gender_name: null,
  series_and_number: '',
  issued_by: {'ru': ''},
  date_of_issue: null,
  validity: null,
  residence_address: {'ru': ''},
})

const filesData = ref({
  user_id: props.user.id,
  insurance_certificate_file: '',
  insurance_certificate_file_remove: false,
  insurance_certificate_file_name: '',
  insurance_certificate_file_file: '',
  insurance_certificate: {'ru': ''},

  disability_group_file: '',
  disability_group_file_remove: false,
  disability_group_file_name: '',
  disability_group_file_file: '',
  disability_group_id: '',
  disability_group_name: '',

  snils_file: '',
  snils_file_remove: false,
  snils_file_name: '',
  snils_file_file: '',
  snils: {'ru': ''},

  medical_book_file: '',
  medical_book_file_remove: false,
  medical_book_file_name: '',
  medical_book_file_file: '',
  medical_book: {'ru': ''},

  migration_card_file_1: '',
  migration_card_file_1_remove: false,
  migration_card_file_1_name: '',
  migration_card_file_1_file: '',

  migration_account_file_1: '',
  migration_account_file_1_remove: false,
  migration_account_file_1_name: '',
  migration_account_file_1_file: '',

  rvp_file_1: '',
  rvp_file_1_remove: false,
  rvp_file_1_name: '',
  rvp_file_1_file: '',

  vnj_file_1: '',
  vnj_file_1_remove: false,
  vnj_file_1_name: '',
  vnj_file_1_file: '',

  payment_check_file_1: '',
  payment_check_file_1_remove: false,
  payment_check_file_1_name: '',
  payment_check_file_1_file: '',

  inn_file: '',
  inn_file_remove: false,
  inn_file_name: '',
  inn_file_file: '',
  inn: {'ru': ''},
})

const cardData = ref({
  user_id: props.user.id,
  currency_id: 1,
  recipient: {'ru': ''},
  recipient_bank: {'ru': ''},
  bik: {'ru': ''},
  correspondent_account: {'ru': ''},
  kpp: {'ru': ''},
  swift_code: '',
})

const emit = defineEmits(['close'])

const showBlock = ref('')

const openBlock = (type) => {
  if(showBlock.value == type) {
    showBlock.value = ''
  } else {
    showBlock.value = type
  }
}

const setCitizenshipFunc = async () => {
  const info = await setCitizenship(citizenshipData.value, '/users/worker/edit/set_citizenship');
  if(info?.data) {
    getInfo()
  }
  showBlock.value = ''
}

const setPassportFunc = async () => {
  const info = await setPassport(passportData.value, '/users/worker/edit/set_passport');
  if(info?.data) {
    getInfo()
  }
  showBlock.value = ''
}

const setCardFunc = async () => {
  const info = await setBankCard(cardData.value, '/users/worker/edit/set_bank_card');
  if(info?.data) {
    getInfo()
  }
  showBlock.value = ''
}


const setFilesFunc = async () => {
  const info = await setFiles(filesData.value, '/users/worker/edit/set_files');
  if(info?.data) {
    getInfo()
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

const openUploadFiles = (id) => {
  document.getElementById(id).click()
}

const updateFile = (event, field) => {
  let file = '';

  if(['passport_file_1', 'passport_file_2', 'passport_file_3', 'passport_translation_file_1', 'passport_translation_file_2', 'passport_translation_file_3'].includes(field)) {

    passportData.value[field] = event.target.files[0]
    passportData.value[field + '_name'] = event.target.files[0].name
    file = event.target.files[0]

    const reader = new FileReader();
    reader.onload = (e) => {
      passportData.value[field] = e.target.result;
    }

    reader.readAsDataURL(file);

  }
   else if(['insurance_certificate_file', 'disability_group_file', 'medical_book_file', 'migration_card_file_1', 'migration_account_file_1', 'rvp_file_1', 'vnj_file_1', 'payment_check_file_1', 'snils_file', 'inn_file'].includes(field)) {

    filesData.value[field] = event.target.files[0]
    filesData.value[field + '_name'] = event.target.files[0].name
    file = event.target.files[0]

    const reader = new FileReader();
    reader.onload = (e) => {
      filesData.value[field] = e.target.result;
    }

    reader.readAsDataURL(file);
  }

}

const getInfo = () => {
  axios.get(route('get_worker_info', {'user_id': props.user.id})).then((response) => {

    citizenshipData.value.citizenship_id   = response.data.data?.citizenship_id
    citizenshipData.value.citizenship_name = response.data.data?.citizenship?.name_c

    passportData.value.passport_file_1 = ''
    passportData.value.passport_file_1_file = response.data.data.passport_file_1
    passportData.value.passport_file_1_remove = false
    passportData.value.passport_file_1_name = ''

    passportData.value.passport_file_2 = ''
    passportData.value.passport_file_2_file = response.data.data.passport_file_2
    passportData.value.passport_file_2_remove = false
    passportData.value.passport_file_2_name = ''

    passportData.value.passport_file_3 = ''
    passportData.value.passport_file_3_file = response.data.data.passport_file_3
    passportData.value.passport_file_3_remove = false
    passportData.value.passport_file_3_name = ''

    passportData.value.passport_translation_file_1 = ''
    passportData.value.passport_translation_file_1_file = response.data.data.passport_translation_file_1
    passportData.value.passport_translation_file_1_remove = false
    passportData.value.passport_translation_file_1_name = ''

    passportData.value.passport_translation_file_2 = ''
    passportData.value.passport_translation_file_2_file = response.data.data.passport_translation_file_2
    passportData.value.passport_translation_file_2_remove = false
    passportData.value.passport_translation_file_2_name = ''

    passportData.value.passport_translation_file_3 = ''
    passportData.value.passport_translation_file_3_file = response.data.data.passport_translation_file_3
    passportData.value.passport_translation_file_3_remove = false
    passportData.value.passport_translation_file_3_name = ''

    passportData.value.first_name        = { ru: response.data.data?.first_name?.ru }
    passportData.value.last_name         = { ru: response.data.data?.last_name?.ru }
    passportData.value.surname           = { ru: response.data.data?.surname?.ru }
    passportData.value.birthday          = response.data.data?.birthday
    passportData.value.place_of_birth    = { ru: response.data.data?.place_of_birth?.ru }
    passportData.value.gender_id         = response.data.data?.gender_id
    passportData.value.gender_name       = response.data.data?.gender?.name_g
    passportData.value.series_and_number = response.data.data?.series_and_number
    passportData.value.issued_by         = { ru: response.data.data?.issued_by?.ru }
    passportData.value.date_of_issue     = response.data.data?.date_of_issue
    passportData.value.validity          = response.data.data?.validity
    passportData.value.residence_address = { ru: response.data.data?.residence_address?.ru }

    filesData.value.insurance_certificate_file = ''
    filesData.value.insurance_certificate_file_file = response.data.data.insurance_certificate_file
    filesData.value.insurance_certificate_file_remove = false
    filesData.value.insurance_certificate_file_name = ''
    filesData.value.insurance_certificate =  { ru: response.data.data?.insurance_certificate?.ru }

    filesData.value.disability_group_file = ''
    filesData.value.disability_group_file_file = response.data.data.disability_group_file
    filesData.value.disability_group_file_remove = false
    filesData.value.disability_group_file_name = ''
    filesData.value.disability_group_id =  response.data.data?.disability_group_id
    filesData.value.disability_group_name =  response.data.data?.disability_group?.name_d

    filesData.value.snils_file = ''
    filesData.value.snils_file_file = response.data.data.snils_file
    filesData.value.snils_file_remove = false
    filesData.value.snils_file_name = ''
    filesData.value.snils =  { ru: response.data.data?.snils?.ru }

    filesData.value.medical_book_file = ''
    filesData.value.medical_book_file_file = response.data.data.medical_book_file
    filesData.value.medical_book_file_remove = false
    filesData.value.medical_book_file_name = ''
    filesData.value.medical_book =  { ru: response.data.data?.medical_book?.ru }

    filesData.value.migration_card_file_1 = ''
    filesData.value.migration_card_file_1_file = response.data.data.migration_card_file_1
    filesData.value.migration_card_file_1_remove = false
    filesData.value.migration_card_file_1_name = ''

    filesData.value.migration_account_file_1 = ''
    filesData.value.migration_account_file_1_file = response.data.data.migration_account_file_1
    filesData.value.migration_account_file_1_remove = false
    filesData.value.migration_account_file_1_name = ''

    filesData.value.rvp_file_1 = ''
    filesData.value.rvp_file_1_file = response.data.data.rvp_file_1
    filesData.value.rvp_file_1_remove = false
    filesData.value.rvp_file_1_name = ''

    filesData.value.vnj_file_1 = ''
    filesData.value.vnj_file_1_file = response.data.data.vnj_file_1
    filesData.value.vnj_file_1_remove = false
    filesData.value.vnj_file_1_name = ''

    filesData.value.payment_check_file_1 = ''
    filesData.value.payment_check_file_1_file = response.data.data.payment_check_file_1
    filesData.value.payment_check_file_1_remove = false
    filesData.value.payment_check_file_1_name = ''

    filesData.value.inn_file = ''
    filesData.value.inn_file_file = response.data.data.inn_file
    filesData.value.inn_file_remove = false
    filesData.value.inn_file_name = ''
    filesData.value.inn =  { ru: response.data.data?.inn?.ru }

  }).catch(error => {

  });
}

getInfo()

const changeCitizenship = (e) => {

};



const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
};

const masks = {
  input: 'YYYY-MM-DD',
};

</script>
