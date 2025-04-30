var lang = document.getElementsByTagName("html")[0].getAttribute("lang");

import json_ru from '../../../lang/php_ru.json';
import json_en from '../../../lang/php_en.json';

var json = {};
if(lang == 'ru') {
  json = json_ru
} else {
  json = json_en
}

const statuses = [
  {
    name: json['general.DRAFT'],
    value: 'DRAFT'
  },
  {
    name: json['general.SENDED'],
    value: 'SENDED'
  },
  {
    name: json['general.CONFIRMED'],
    value: 'CONFIRMED'
  },
  {
    name: json['general.PASSIVATED'],
    value: 'PASSIVATED'
  },
  {
    name: json['general.REJECTED'],
    value: 'REJECTED'
  }
]

export function useEstabilishmentStatuses() {
  return {
    statuses,
  }
}

