var lang = document.getElementsByTagName("html")[0].getAttribute("lang");

import json_ru from '../../../lang/php_ru.json';
import json_en from '../../../lang/php_en.json';

var json = {};
if(lang == 'ru') {
  json = json_ru
} else {
  json = json_en
}

export function useBalanceTypeText(text) {
  let status = '';
  switch (text) {
    case 'SHIFT':
      status = json['general.SHIFT']
      break;
    case 'BONUS':
      status = json['general.BONUS']
      break;
    case 'TRANSFER':
      status = json['general.TRANSFER']
      break;
  }

  return status;
}
