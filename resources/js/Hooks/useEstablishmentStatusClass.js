export function useEstablishmentStatusClass(status) {
  let statusClass = ''

  switch(status) {
    case 'CONFIRMED':
      statusClass = 'text-success'
      break;
    case 'REJECTED':
      statusClass = 'text-danger'
      break;
    case 'PASSIVATED':
        statusClass = 'text-warning'
        break;
    default:
      statusClass = 'text-primary'
  }

  return statusClass
}

