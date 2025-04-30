var lang = document.getElementsByTagName("html")[0].getAttribute("lang");

import json_ru from '../../../lang/php_ru.json';
import json_en from '../../../lang/php_en.json';

var json = {};
if(lang == 'ru') {
  json = json_ru
} else {
  json = json_en
}

export function usePermissionsText(text) {
  let status = '';
  switch (text) {
    case 'ADMIN':
      status = json['general.admin']
      break;
    case 'WORKER':
      status = json['general.employee']
      break;
    case 'COMPANY':
      status = json['general.company']
      break;
    case 'MODERATOR':
      status = json['general.moderator']
      break;
    case 'ADMINISTRATOR':
      status = json['general.administrator']
      break;
  }

  return status;
}
