import { defineStore } from 'pinia'

interface BookParams {
  isbn?: string
  publish?: string[] // Change the type to string[]
  producer?: string[] // Change the type to string[]
  series?: string[] // Change the type to string[]
  buy_date_from?: string
  buy_date_to?: string
  buy_price_from?: number
  buy_price_to?: number
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
    }
  }
})
