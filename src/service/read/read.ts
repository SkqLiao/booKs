import { hyRequest } from '../index'

import { IDataType } from './types'

export function addReadingRecord(params: object) {
  return hyRequest.post<IDataType<string>>({
    url: '/read/add.php',
    data: params
  })
}

export async function getInfo(
  func: (params: any) => Promise<IDataType>,
  params: object
) {
  try {
    const response = await func(params)
    if (response.code !== 200) {
      console.log(response.message)
      console.log(response?.error)
      console.log(response?.sql)
      throw new Error(response.message)
    }
    //console.log(response.data)
    return response.data
  } catch (error) {
    console.log(error)
  }
}
