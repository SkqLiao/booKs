import { hyRequest, hyRequest2 } from '../index'

import { IDataType, Ibook, DoubanAPI, IQueryParams } from './types'

export function getRequest(params: IQueryParams) {
  return hyRequest.get<IDataType<any[]>>({
    url: '/get.php',
    params: params
  })
}

export function bookCoverRequest(params: { isbn: string }) {
  return hyRequest.get<IDataType<string>>({
    url: '/book/getImage.php',
    params: params
  })
}

export function bookInfoUpdate(params: Ibook) {
  return hyRequest.post<IDataType<string>>({
    url: '/book/update.php',
    data: params
  })
}

export function bookInfoDelete(params: { isbn: string }) {
  return hyRequest.delete<IDataType<string>>({
    url: '/book/delete.php',
    params: params
  })
}

export function bookDoubanRequest(isbn: string) {
  return hyRequest2.get<DoubanAPI>({
    url: '/isbn/' + isbn
  })
}

export function bookAddRequest(params: Ibook) {
  return hyRequest.post<IDataType<string>>({
    url: '/book/add.php',
    data: params
  })
}

export function addReadingRecord(params: object) {
  return hyRequest.post<IDataType<string>>({
    url: '/read/add.php',
    data: params
  })
}

export function addReadingExcerpt(params: object) {
  return hyRequest.post<IDataType<string>>({
    url: '/excerpt/add.php',
    data: params
  })
}

export async function getInfo(
  func: (params: IQueryParams) => Promise<IDataType<any[]>>,
  params: IQueryParams
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
