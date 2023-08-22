// service统一出口
import HYRequest from './request'
import { BASE_URL, BASE_URL2, TIME_OUT } from './request/config'

const hyRequest = new HYRequest({
  baseURL: BASE_URL,
  timeout: TIME_OUT
})

const hyRequest2 = new HYRequest({
  baseURL: BASE_URL2,
  timeout: TIME_OUT
})

export { hyRequest, hyRequest2 }
