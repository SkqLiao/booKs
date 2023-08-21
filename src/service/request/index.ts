import axios from 'axios'
import type { AxiosInstance, InternalAxiosRequestConfig } from 'axios'
import type { HYRequestInterceptors, HYRequestConfig } from './type'

const DEAFULT_LOADING = true

class HYRequest {
  instance: AxiosInstance
  interceptors: HYRequestInterceptors
  constructor(config: HYRequestConfig) {
    this.instance = axios.create(config)

    this.interceptors = config.interceptors as HYRequestInterceptors

    this.instance.interceptors.request.use(
      this.interceptors?.requestInterceptor,
      this.interceptors?.requestInterceptorCatch
    )
    this.instance.interceptors.response.use(
      this.interceptors?.responseInterceptor,
      this.interceptors?.responseInterceptorCatch
    )

    this.instance.interceptors.request.use(
      (config) => {
        //console.log('所有的实例都有的拦截器: 请求成功拦截')
        return config
      },
      (err) => {
        //console.log('所有的实例都有的拦截器: 请求失败拦截')
        return err
      }
    )

    this.instance.interceptors.response.use(
      (res) => {
        //console.log('所有的实例都有的拦截器: 响应成功拦截')

        const data = res.data
        if (data.returnCode === '-1001') {
          console.log('请求失败~, 错误信息')
        } else {
          return data
        }
      },
      (err) => {
        console.log('所有的实例都有的拦截器: 响应失败拦截')

        // 例子: 判断不同的HttpErrorCode显示不同的错误信息
        if (err.response.status === 404) {
          console.log('404的错误~')
        }
        return err
      }
    )
  }
  request<T>(config: HYRequestConfig<T>): Promise<T> {
    return new Promise((resolve, reject) => {
      // 1.单个请求对请求config的处理
      if (config.interceptors?.requestInterceptor) {
        config = config.interceptors.requestInterceptor(
          config as InternalAxiosRequestConfig
        )
      }

      this.instance
        .request<any, T>(config)
        .then((res) => {
          // 1.单个请求对数据的处理
          if (config.interceptors?.responseInterceptor) {
            res = config.interceptors.responseInterceptor(res)
          }

          // 3.将结果resolve返回出去
          resolve(res)
        })
        .catch((err) => {
          reject(err)
          return err
        })
    })
  }
  get<T>(config: HYRequestConfig<T>): Promise<T> {
    return this.request<T>({ ...config, method: 'GET' })
  }

  post<T>(config: HYRequestConfig<T>): Promise<T> {
    return this.request<T>({ ...config, method: 'POST' })
  }

  delete<T>(config: HYRequestConfig<T>): Promise<T> {
    return this.request<T>({ ...config, method: 'DELETE' })
  }

  patch<T>(config: HYRequestConfig<T>): Promise<T> {
    return this.request<T>({ ...config, method: 'PATCH' })
  }
}

export default HYRequest
