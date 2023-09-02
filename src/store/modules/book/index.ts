import { defineStore } from 'pinia'

export interface BookParams {
  title?: string
  isbn?: string
  author?: string[]
  publish?: string[]
  producer?: string[]
  series?: string[]
  buy_date_from?: string
  buy_date_to?: string
  buy_price_from?: number
  buy_price_to?: number
  buy_pos?: string[]
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
    },
    buildQueryConditions(): string[] {
      const conditions = []

      // 根据 title 字段构建条件
      if (this.params.title) {
        conditions.push(`title LIKE '%${this.params.title}%'`)
      }

      // 根据 isbn 字段构建条件
      if (this.params.isbn) {
        conditions.push(`isbn = '${this.params.isbn}'`)
      }

      // 根据 author 字段构建条件
      if (this.params.author && this.params.author.length > 0) {
        const authorConditions = this.params.author.map(
          (author) => `author LIKE '%${author}%'`
        )
        conditions.push(`(${authorConditions.join(' OR ')})`)
      }

      // 根据 publish 字段构建条件
      if (this.params.publish && this.params.publish.length > 0) {
        const publishConditions = this.params.publish.map(
          (publish) => `publish IN ('${publish}')`
        )
        conditions.push(`(${publishConditions.join(' OR ')})`)
      }

      if (this.params.producer && this.params.producer.length > 0) {
        const producerConditions = this.params.producer.map(
          (producer) => `producer IN ('${producer}')`
        )
        conditions.push(`(${producerConditions.join(' OR ')})`)
      }

      if (this.params.series && this.params.series.length > 0) {
        const seriesConditions = this.params.series.map(
          (series) => `series IN ('${series}')`
        )
        conditions.push(`(${seriesConditions.join(' OR ')})`)
      }

      if (this.params.buy_date_from && this.params.buy_date_to) {
        conditions.push(
          `buy_date BETWEEN '${this.params.buy_date_from}' AND '${this.params.buy_date_to}'`
        )
      }

      if (this.params.buy_price_from && this.params.buy_price_to) {
        conditions.push(
          `real_price BETWEEN ${this.params.buy_price_from} AND ${this.params.buy_price_to}`
        )
      }

      if (this.params.buy_pos && this.params.buy_pos.length > 0) {
        const buyPosConditions = this.params.buy_pos.map(
          (buy_pos) => `buy_pos IN ('${buy_pos}')`
        )
        conditions.push(`(${buyPosConditions.join(' OR ')})`)
      }

      return conditions
    }
  }
})
