var lang = document.getElementsByTagName("html")[0].getAttribute("lang");

import { trans } from 'laravel-vue-i18n'

import json_ru from '../../../lang/php_ru.json';
import json_en from '../../../lang/php_en.json';

var json = {};
if(lang == 'ru') {
  json = json_ru
} else {
  json = json_en
}

export function useShiftReasonText(text) {
  let status = '';
  switch (text) {
    case 'CONFIRMATION':
      status = json['general.CONFIRMATION']
      break;
    case 'CANCELED':
      status = json['general.CANCELED']
      break;
  }

  return status;
}
