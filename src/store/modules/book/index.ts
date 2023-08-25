import { defineStore } from 'pinia'

export interface BookParams {
  isbn?: string
  author?: string[]
  publish?: string[]
  producer?: string[]
  series?: string[]
  buy_date_from?: string
  buy_date_to?: string
  buy_price_from?: number
  buy_price_to?: number
  [key: string]: string | string[] | number | undefined
}

export const useBookStore = defineStore('book', {
  state: (): { params: BookParams } => ({
    params: {}
  }),
  getters: {
    getParams(): BookParams {
      return this.params
    }
  },
  actions: {
    setParams(params: BookParams) {
      this.params = params
    },
    removeParams(param: string) {
      if (this.params[param]) delete this.params[param]
    }
  }
})
