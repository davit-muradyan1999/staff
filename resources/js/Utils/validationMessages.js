var lang = document.getElementsByTagName("html")[0].getAttribute("lang");

import { trans } from 'laravel-vue-i18n';

import json_ru from '../../../lang/php_ru.json';
import json_en from '../../../lang/php_en.json';

var json = {};
if(lang == 'ru') {
  json = json_ru
} else {
  json = json_en
}

import { helpers, required, numeric, email, decimal, requiredIf } from '@vuelidate/validators'

/* Start Custom Validation */
const AboveZeroCheck = (value) => {
  return value == 0 ? false : true
}

const ImageExtensionCheck = (value) => {
  if(value === undefined || value === null || value === '' || typeof value === 'string') return true
  return ['image/jpeg', 'image/jpg', 'image/gif', 'image/png', 'image/svg', 'image/svg+xml'].includes(value.type)
};

const MinLengthCheck = (param, value) => {
  if(value === undefined || value === null || value === '') return true
  return param < value.length
}

const MaxLengthCheck = (param, value) => {
  if(value === undefined || value === null) return true
  return value.length <= param
}
/* End Custom Validation */

export const Required = helpers.withMessage(
  json['messages.required'],
  required,
)

export const RequiredIf = helpers.withMessage(
  json['messages.required'],
  requiredIf,
);

export const Numeric = helpers.withMessage(
  json['messages.numeric'],
  numeric,
)

export const Email = helpers.withMessage(
  json['messages.emailFormat'],
  email,
)

export const ImageExtension = helpers.withMessage(
  json['messages.wrongImgExtension'],
  ImageExtensionCheck,
)

export const AboveZero = helpers.withMessage(
  json['messages.aboveZero'],
  AboveZeroCheck,
)

export const MinLength = (param, value) => helpers.withParams(
  { message: trans(json['messages.minLength'], { number: param }) },
  (value) => MinLengthCheck(param, value)
)

export const MaxLength = (param, value) => helpers.withParams(
  { message: trans(json['messages.maxLength'], { number: param }) },
  (value) => MaxLengthCheck(param, value)
)

