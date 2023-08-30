import { hyRequest } from '../index'

import { IDataType, IRecord } from './types'

export function readingInfoRequest(params: object) {
  return hyRequest.get<IDataType<IRecord[]>>({
    url: '/read/get.php',
    params: params
  })
}

export function bookInfoRequest(params: object) {
  return hyRequest.get<IDataType<any>>({
    url: '/book/query.php',
    params: params
  })
}

export function readingDetailRequest(params: object) {
  return hyRequest.get<IDataType<IRecord[]>>({
    url: '/read/query.php',
    params: params
  })
}

export async function getInfo(
  func: (params: object) => Promise<IDataType>,
  params: object
) {
  try {
    const response = await func(params)
    if (response.code !== 200) {
      console.log(response.response)
      throw new Error(response.response)
    }
    //console.log(response.data)
    return response.data
  } catch (error) {
    console.log(error)
    throw new Error(error as string)
  }
}
