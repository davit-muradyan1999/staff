import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'

export const setWhoAreWe = async (data, url = '/set_who_are_we') => {
  let resp = null
  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setIndustry = async (data, url = '/set_industry') => {
  let resp = null
  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setContact = async (data, url = '/set_contact') => {
  let resp = null
  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setAdvantage = async (data, url = '/set_advantage', userId = null) => {
  let resp = null

  let obj = {
    data: data
  }

  if(userId) {
    obj.user_id = userId
  }

  await axios.post(url, obj).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setCard = async (data, url = '/', userId = null) => {
  let resp = null

  if(userId) {
    data.user_id = userId
  }

  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setAccount = async (data, url = '/', userId = null) => {
  let resp = null

  if(userId) {
    data.user_id = userId
  }

  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setFiles = async (data, url = '/', userId = null) => {
  let resp = null

  if(userId) {
    data.user_id = userId
  }

  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setUserInfo = async (data, url = '/', userId = null) => {
  let resp = null

  if(userId) {
    data.user_id = userId
  }

  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}

export const setCompanyInfo = async (data, url = '/', userId = null) => {
  let resp = null

  if(userId) {
    data.user_id = userId
  }

  await axios.post(url, data).then(response => {
    resp = response.data
    notify({
      title: response.data.message,
      type: 'success'
    });
  }).catch(error => {
    console.log(error.response.data)
    notify({
      title: error.response.data.message,
      type: 'error'
    });
  });

  return resp;
}
