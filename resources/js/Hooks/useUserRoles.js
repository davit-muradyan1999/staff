const roles = [
  {
    name: 'Админ',
    value: 'ADMIN'
  },
  {
    name: 'Работник',
    value: 'WORKER'
  },
  {
    name: 'Компания',
    value: 'COMPANY'
  },
  {
    name: 'Модератор',
    value: 'MODERATOR'
  },
  {
    name: 'Администратор',
    value: 'ADMINISTRATOR'
  }
]

export function useUserRoles() {
  return {
    roles,
  }
}

