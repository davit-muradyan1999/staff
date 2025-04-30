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

export function useStatusText(text) {
  let status = '';
  switch (text) {
    case 'DRAFT':
      status = json['general.DRAFT']
      break;
    case 'SENDED':
      status = json['general.SENDED']
      break;
    case 'CONFIRMED':
      status = json['general.CONFIRMED']
      break;
    case 'PASSIVATED':
      status = json['general.PASSIVATED']
      break;
    case 'REJECTED':
      status = json['general.REJECTED']
      break;
  }

  return status;
}
