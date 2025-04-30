const types = [
  {
    name: 'Получить список платежных реестров за временной промежуток',
    value: 'SALARY_GET_PAYMENT_REGISTRY_LIST'
  }
  // {
  //   name: 'Получить результат добавления сотрудника по реквизитам',
  //   value: 'ADD_EMPLOYEE_RECIPIENT'
  // },
  // {
  //   name: 'Получить результат создания черновиков анкет сотрудников',
  //   value: 'SALARY_GET_EMPLOYEE_CREATE'
  // },
  // {
  //   name: 'Получить результат создания черновика платежного реестра',
  //   value: 'SALARY_GET_PAYMENT_REGISTRY_CREATE'
  // },
  // {
  //   name: 'Получить информацию по платежному реестру',
  //   value: 'SALARY_GET_PAYMENT_REGISTRY_CREATE'
  // },
  // {
  //   name: 'Получить результат создания черновика платежного реестра',
  //   value: 'SALARY_GET_PAYMENT_REGISTRY'
  // },
  // {
  //   name: 'Получить результат подписания платежного реестра сотрудников',
  //   value: 'SALARY_PAYMENT_REGISTRY_SUBMIT'
  // }
]

export function useSalaryProjectResultTypes() {
  return {
    types,
  }
}

