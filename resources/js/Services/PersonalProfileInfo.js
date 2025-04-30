import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'

export const setPreferredPositions = async (data) => {
  let resp = null
  await axios.post('/set_preferred_positions', {
    data: data
  }).then(response => {
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

export const setExperiences = async (data) => {
  let resp = null
  await axios.post('/set_experiences', {
    data: data
  }).then(response => {
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

export const setFieldOfActivities = async (data) => {
  let resp = null
  await axios.post('/set_field_of_activities', {
    data: data
  }).then(response => {
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

export const setEducations = async (data) => {
  let resp = null
  await axios.post('/set_educations', {
    data: data
  }).then(response => {
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

export const setProfessionalSkills = async (data) => {
  let resp = null
  await axios.post('/set_professional_skills', {
    data: data
  }).then(response => {
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

export const setPersonalSkills = async (data) => {
  let resp = null
  await axios.post('/set_personal_skills', {
    data: data
  }).then(response => {
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

export const setLanguageSkills = async (data) => {
  let resp = null
  await axios.post('/set_language_skills', {
    data: data
  }).then(response => {
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

export const setDriverLicenses = async (data) => {
  let resp = null
  await axios.post('/set_driver_licenses', {
    data: data
  }).then(response => {
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

export const setHobbies = async (data) => {
  let resp = null
  await axios.post('/set_hobbies', {
    data: data
  }).then(response => {
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

export const setCitizenship = async (data, url = '/', userId = null) => {
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

export const setPassport = async (data, url = '/', userId = null) => {
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

export const setBankCard = async (data, url = '/', userId = null) => {
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
