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
                          <p class="mb-0">КАРТОЧКА ОРГАНИЗАЦИИ</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="organization_card"
                              ref="icon"
                              @change="updateFile($event, 'organization_card')"
                            >

                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('organization_card')"
                                  :value="card?.organization_card ? card?.organization_card_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="card?.organization_card_file">
                                  <a target="_blank" :href="`/storage/${card?.organization_card_file}`" :class="{'opacity-50': card.organization_card_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="card.organization_card_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="card.organization_card_remove = !card.organization_card_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="card?.organization_card">
                              <span class="text-danger cursor-pointer" @click="card.organization_card = null, card.organization_card_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="card?.organization_card_file">
                              <a target="_blank" :href="`/storage/${card?.organization_card_file}`">
                                <font-awesome-icon icon="file-lines" class="list-info" />
                              </a>
                            </template>
                            <template v-else>

                            </template>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
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
                          <p class="mb-0">ИНН</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.inn['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.inn?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">ОГРН</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.ogrn['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.ogrn?.ru }}</span>
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
                            <input type="text" class="form-control" v-model="card.kpp['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.kpp?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">АДРЕС</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.address['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.address?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">ОКПО</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.okpo['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.okpo?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">ОКВЭД</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.okved['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.okved?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">ОКОПФ</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.okopf['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.okopf?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">ОКФС</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'card'">
                            <input type="text" class="form-control" v-model="card.okfs['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ card?.okfs?.ru }}</span>
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
                          <p class="mb-0">РАСЧЕТНЫЕ СЧЕТА</p>
                        </div>
                        <div class="col-sm-8">

                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
                            <template v-if="showBlock == 'account'">
                              <font-awesome-icon icon="check" class="fs-3 me-2 check-icon" @click="setAccountFunc" />
                              <font-awesome-icon icon="times" class="fs-3" @click="openBlock('account')" />
                            </template>
                            <template v-else>
                              <font-awesome-icon icon="pen-to-square" class="fs-3" @click="openBlock('account')" />
                            </template>
                          </div>
                        </div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">Наименование банка</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'account'">
                            <input type="text" class="form-control" v-model="account.bank_name['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ account?.bank_name?.ru }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">Расчетный счет</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'account'">
                            <input type="text" class="form-control" v-model="account.checking_account">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ account?.checking_account }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">Корреспондентский счет</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'account'">
                            <input type="text" class="form-control" v-model="account.correspondent_account">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ account?.correspondent_account }}</span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">БИК банка</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'account'">
                            <input type="text" class="form-control" v-model="account.bank_bip['ru']">
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">{{ account?.bank_bip?.ru }}</span>
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
                          <p class="mb-0">Устав</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="charter"
                              ref="icon"
                              @change="updateFile($event, 'charter')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('charter')"
                                  :value="files?.charter ? files?.charter_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.charter_file">
                                  <a target="_blank" :href="`/storage/${files?.charter_file}`" :class="{'opacity-50': files.charter_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.charter_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.charter_remove = !files.charter_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.charter">
                              <span class="text-danger cursor-pointer" @click="files.charter = null, files.charter_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <span class="text-muted mb-0">
                              <template v-if="files?.charter_file">
                                <a target="_blank" :href="`/storage/${files?.charter_file}`">
                                  <font-awesome-icon icon="file-lines" class="list-info" />
                                </a>
                              </template>
                              <template v-else>

                              </template>
                            </span>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end">
                          <div class="profile-save-icon h-100">
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
                      <div class="row">
                        <div class="col-sm-3">
                          <p class="mb-0">Свидетельство о постановке на учет в налоговом органе</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="tax_certificate"
                              ref="icon"
                              @change="updateFile($event, 'tax_certificate')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('tax_certificate')"
                                  :value="files?.tax_certificate ? files?.tax_certificate_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.tax_certificate_file">
                                  <a target="_blank" :href="`/storage/${files?.tax_certificate_file}`" :class="{'opacity-50': files.tax_certificate_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.tax_certificate_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.tax_certificate_remove = !files.tax_certificate_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.tax_certificate">
                              <span class="text-danger cursor-pointer" @click="files.tax_certificate = null, files.tax_certificate_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="files?.tax_certificate_file">
                              <a target="_blank" :href="`/storage/${files?.tax_certificate_file}`">
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
                          <p class="mb-0">Выписка из ЕГРЮЛ</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="extract_egrul"
                              ref="icon"
                              @change="updateFile($event, 'extract_egrul')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('extract_egrul')"
                                  :value="files?.extract_egrul ? files?.extract_egrul_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.extract_egrul_file">
                                  <a target="_blank" :href="`/storage/${files?.extract_egrul_file}`" :class="{'opacity-50': files.extract_egrul_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.extract_egrul_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.extract_egrul_remove = !files.extract_egrul_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.extract_egrul">
                              <span class="text-danger cursor-pointer" @click="files.extract_egrul = null, files.extract_egrul_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="files?.extract_egrul_file">
                              <a target="_blank" :href="`/storage/${files?.extract_egrul_file}`">
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
                          <p class="mb-0">ПАСПОРТ ГЕН ДИРЕКТОРА</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="passport_gen_director"
                              ref="icon"
                              @change="updateFile($event, 'passport_gen_director')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('passport_gen_director')"
                                  :value="files?.passport_gen_director ? files?.passport_gen_director_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.passport_gen_director_file">
                                  <a target="_blank" :href="`/storage/${files?.passport_gen_director_file}`" :class="{'opacity-50': files.passport_gen_director_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.passport_gen_director_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.passport_gen_director_remove = !files.passport_gen_director_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.passport_gen_director">
                              <span class="text-danger cursor-pointer" @click="files.passport_gen_director = null, files.passport_gen_director_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="files?.passport_gen_director_file">
                              <a target="_blank" :href="`/storage/${files?.passport_gen_director_file}`">
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
                          <p class="mb-0">СПРАВКУ ОБ ОТСУТСТВИИ ЗАДОЛЖЕННОСТИ</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="certificate_of_no_debt"
                              ref="icon"
                              @change="updateFile($event, 'certificate_of_no_debt')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('certificate_of_no_debt')"
                                  :value="files?.certificate_of_no_debt ? files?.certificate_of_no_debt_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.certificate_of_no_debt_file">
                                  <a target="_blank" :href="`/storage/${files?.certificate_of_no_debt_file}`" :class="{'opacity-50': files.certificate_of_no_debt_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.certificate_of_no_debt_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.certificate_of_no_debt_remove = !files.certificate_of_no_debt_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.certificate_of_no_debt">
                              <span class="text-danger cursor-pointer" @click="files.certificate_of_no_debt = null, files.certificate_of_no_debt_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="files?.certificate_of_no_debt_file">
                              <a target="_blank" :href="`/storage/${files?.certificate_of_no_debt_file}`">
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
                          <p class="mb-0">НАЛОГОВЫЕ ДЕКЛАРАЦИИ ЗА ПРОШЛЫЙ ГОД</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="tax_declarations_last_year"
                              ref="icon"
                              @change="updateFile($event, 'tax_declarations_last_year')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('tax_declarations_last_year')"
                                  :value="files?.tax_declarations_last_year ? files?.tax_declarations_last_year_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.tax_declarations_last_year_file">
                                  <a target="_blank" :href="`/storage/${files?.tax_declarations_last_year_file}`" :class="{'opacity-50': files.tax_declarations_last_year_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.tax_declarations_last_year_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.tax_declarations_last_year_remove = !files.tax_declarations_last_year_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.tax_declarations_last_year">
                              <span class="text-danger cursor-pointer" @click="files.tax_declarations_last_year = null, files.tax_declarations_last_year_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="files?.tax_declarations_last_year_file">
                              <a target="_blank" :href="`/storage/${files?.tax_declarations_last_year_file}`">
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
                          <p class="mb-0">НАЛОГОВЫЕ ДЕКЛАРАЦИИ ЗА ПОСЛЕДНИЙ ОТЧЕТНЫЙ ПЕРИОД ТЕКУЩЕГО ГОДА</p>
                        </div>
                        <div class="col-sm-8">
                          <template v-if="showBlock == 'files'">
                            <input
                              type="file"
                              class="form-control d-none"
                              id="tax_declarations_last_period"
                              ref="icon"
                              @change="updateFile($event, 'tax_declarations_last_period')"
                            >
                            <div class="row">
                              <div class="col-md-11">
                                <input
                                  type="text"
                                  @click="openUploadFiles('tax_declarations_last_period')"
                                  :value="files?.tax_declarations_last_period ? files?.tax_declarations_last_period_name : 'Выбрать файл'"
                                  readonly
                                  class="form-control cursor-pointer"
                                >
                              </div>
                              <div class="col-md-1 p-2">
                                <template v-if="files?.tax_declarations_last_period_file">
                                  <a target="_blank" :href="`/storage/${files?.tax_declarations_last_period_file}`" :class="{'opacity-50': files.tax_declarations_last_period_remove}">
                                    <font-awesome-icon icon="file-lines" class="list-info" />
                                  </a>
                                  <font-awesome-icon :icon="files.tax_declarations_last_period_remove ? 'rotate' : 'trash'" class="trash-file ms-2" @click="files.tax_declarations_last_period_remove = !files.tax_declarations_last_period_remove" />
                                </template>
                              </div>
                            </div>
                            <template v-if="files?.tax_declarations_last_period">
                              <span class="text-danger cursor-pointer" @click="files.tax_declarations_last_period = null, files.tax_declarations_last_period_name = ''">Удалить</span>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="files?.tax_declarations_last_period_file">
                              <a target="_blank" :href="`/storage/${files?.tax_declarations_last_period_file}`">
                                <font-awesome-icon icon="file-lines" class="list-info" />
                              </a>
                            </template>
                            <template v-else>

                            </template>
                          </template>
                        </div>
                        <div class="col-sm-1 text-end"></div>
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
import {
  setCard,
  setAccount,
  setFiles,
} from '@/Services/CompanyProfileInfo.js'

const props = defineProps({
  user: Object,
  redirectURL: String,
  errorMsg: String,
});

const card = ref({
  user_id: props.user.id,
  inn: {'ru': ''},
  ogrn: {'ru': ''},
  kpp: {'ru': ''},
  address: {'ru': ''},
  okpo: {'ru': ''},
  okved: {'ru': ''},
  okopf: {'ru': ''},
  okfs: {'ru': ''},
  organization_card_file: '',
  organization_card_remove: '',
  organization_card: '',
})

const account = ref({
  user_id: props.user.id,
  inn: '',
  ogrn: '',
  kpp: '',
  address: '',
  bank_name: {'ru': ''},
  checking_account: '',
  correspondent_account: '',
  bank_bip: {'ru': ''},
})

const files = ref({
  user_id: props.user.id,
  charter_file: '',
  charter_remove: false,
  charter: '',
  tax_certificate_file: '',
  tax_certificate_remove: false,
  tax_certificate: '',
  extract_egrul_file: '',
  extract_egrul_remove: false,
  extract_egrul: '',
  passport_gen_director_file: '',
  passport_gen_director_remove: false,
  passport_gen_director: '',
  certificate_of_no_debt_file: '',
  certificate_of_no_debt_remove: false,
  certificate_of_no_debt: '',
  tax_declarations_last_year_file: '',
  tax_declarations_last_year_remove: false,
  tax_declarations_last_year: '',
  tax_declarations_last_period_file: '',
  tax_declarations_last_period_remove: false,
  tax_declarations_last_period: '',
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

const setCardFunc = async () => {
  const info = await setCard(card.value, '/admin/users/company/edit/set_card');
  if(info?.data) {
    card.value.inn['ru']     = info?.data?.inn?.ru
    card.value.ogrn['ru']    = info?.data?.ogrn?.ru
    card.value.kpp['ru']     = info?.data?.kpp?.ru
    card.value.address['ru'] = info?.data?.address?.ru
    card.value.okpo['ru']    = info?.data?.okpo?.ru
    card.value.okved['ru']   = info?.data?.okved?.ru
    card.value.okopf['ru']   = info?.data?.okopf?.ru
    card.value.okfs['ru']    = info?.data?.okfs?.ru

    card.value.organization_card_file = info.data.organization_card
    card.value.organization_card_remove = false
    card.value.organization_card = ''
  }
  showBlock.value = ''
}

const setAccountFunc = async () => {
  const info = await setAccount(account.value, '/admin/users/company/edit/set_account');
  if(info?.status == 'success') {
    card.value.inn['ru']     = info?.data?.inn?.ru
    card.value.ogrn['ru']    = info?.data?.ogrn?.ru
    card.value.kpp['ru']     = info?.data?.kpp?.ru
    card.value.address['ru'] = info?.data?.address?.ru
    showBlock.value = ''
  }
}

const setFilesFunc = async () => {
  const info = await setFiles(files.value, '/admin/users/company/edit/set_files');
  if(info?.data) {
    files.value.charter_file = info.data.charter
    files.value.charter_remove = false
    files.value.charter = ''

    files.value.tax_certificate_file = info.data.tax_certificate
    files.value.tax_certificate_remove = false
    files.value.tax_certificate = ''

    files.value.extract_egrul_file = info.data.extract_egrul
    files.value.extract_egrul_remove = false
    files.value.extract_egrul = ''

    files.value.passport_gen_director_file = info.data.passport_gen_director
    files.value.passport_gen_director_remove = false
    files.value.passport_gen_director = ''

    files.value.certificate_of_no_debt_file = info.data.certificate_of_no_debt
    files.value.certificate_of_no_debt_remove = false
    files.value.certificate_of_no_debt = ''

    files.value.tax_declarations_last_year_file = info.data.tax_declarations_last_year
    files.value.tax_declarations_last_year_remove = false
    files.value.tax_declarations_last_year = ''

    files.value.tax_declarations_last_period_file = info.data.tax_declarations_last_period
    files.value.tax_declarations_last_period_remove = false
    files.value.tax_declarations_last_period = ''
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

  if(field == 'organization_card') {
    card.value.organization_card = event.target.files[0]
    card.value.organization_card_name = event.target.files[0].name
    file = event.target.files[0]

    const reader = new FileReader();
    reader.onload = (e) => {
      card.value[field] = e.target.result;
    }

    reader.readAsDataURL(file);

  }
   else if(['charter', 'tax_certificate', 'extract_egrul', 'passport_gen_director', 'certificate_of_no_debt', 'tax_declarations_last_year', 'tax_declarations_last_period'].includes(field)) {

    files.value[field] = event.target.files[0]
    files.value[field + '_name'] = event.target.files[0].name
    file = event.target.files[0]

    const reader = new FileReader();
    reader.onload = (e) => {
      files.value[field] = e.target.result;
    }

    reader.readAsDataURL(file);
  }

}

const getInfo = () => {
  axios.get(route('admin.get_company_info', {'user_id': props.user.id})).then((response) => {
    card.value = {
      user_id: response.data.data.user_id,
      organization_card_file: response.data.data.organization_card,
      inn: {
        'ru': response.data.data.inn?.ru
      },
      ogrn: {
        'ru': response.data.data.ogrn?.ru
      },
      kpp: {
        'ru': response.data.data.kpp?.ru
      },
      address: {
        'ru': response.data.data.address?.ru
      },
      okpo: {
        'ru': response.data.data.okpo?.ru
      },
      okved: {
        'ru': response.data.data.okved?.ru
      },
      okopf: {
        'ru': response.data.data.okopf?.ru
      },
      okfs: {
        'ru': response.data.data.okfs?.ru
      },
    }

    account.value = {
      user_id: response.data.data.user_id,
      bank_name: {
        'ru': response.data.data.bank_name?.ru
      },
      checking_account: response.data.data.checking_account,
      correspondent_account: response.data.data.correspondent_account,
      bank_bip: {
        'ru': response.data.data.bank_bip?.ru
      },
    }

    files.value = {
      user_id: response.data.data.user_id,
      charter_file: response.data.data.charter,
      tax_certificate_file: response.data.data.tax_certificate,
      extract_egrul_file: response.data.data.extract_egrul,
      passport_gen_director_file: response.data.data.passport_gen_director,
      certificate_of_no_debt_file: response.data.data.certificate_of_no_debt,
      tax_declarations_last_year_file: response.data.data.tax_declarations_last_year,
      tax_declarations_last_period_file: response.data.data.tax_declarations_last_period,
    }

    console.log(card.value)
  }).catch(error => {

  });
}

getInfo()

</script>
