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
