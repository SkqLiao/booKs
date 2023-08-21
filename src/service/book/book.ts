import hyRequest from '../index'

import { IDataType, Ibook, IbookParams } from './types'

export function bookInfoRequest(params: Object) {
  return hyRequest.get<IDataType<Ibook[]>>({
    url: '/book/get.php',
    params: params
  })
}

export function bookNumberRequest(params: Object) {
  return hyRequest.get<IDataType<number>>({
    url: '/book/getCount.php',
    params: params
  })
}

export function bookInfoUpdate(params: Ibook) {
  return hyRequest.post<IDataType<string>>({
    url: '/book/upd.php',
    data: params
  })
}

export function bookInfoDel(isbn: string) {
  return hyRequest.delete<IDataType<string>>({
    url: '/book/del.php',
    params: {
      isbn: isbn
    }
  })
}

export function bookParamRequest(param: string) {
  return hyRequest.get<IDataType<[IbookParams]>>({
    url: '/book/getInfo.php',
    params: {
      query: param
    }
  })
}
