import axios from 'axios'

export const getDistrictByCountry = async (countryId) => {
  const result = await axios.get(`/admin/districts/country/${countryId}`)
  return result.data.districts
}

export const getRegionByDistrict = async (districtId) => {
  const result = await axios.get(`/admin/regions/district/${districtId}`)
  return result.data.regions
}

export const getEstablishmentById = async (establishmentId) => {
  const result = await axios.get(`/establishment/${establishmentId}`)
  return result.data.establishment
}

export const getBranchesByCompanyId = async (companyId) => {
  const result = await axios.get(`/branches/company/${companyId}`)
  return result.data.branches
}

export const getEstablishmentsByCompanyId = async (companyId) => {
  const result = await axios.get(`/establishments/company/${companyId}`)
  return result.data.establishments
}

export const getAdvantageEmployeesByCompanyId = async (companyId) => {
  const result = await axios.get(`/advantage_employees/company/${companyId}`)
  return result.data.advantageEmployees
}
