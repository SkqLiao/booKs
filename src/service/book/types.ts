export interface Ibook {
  id: number
  title: string
  subtitle: string
  original_title: string
  douban_id: number
  isbn: string
  author: string[]
  translator: string[]
  publish: string
  producer: string
  pages: string
  price: number
  binding: string
  series: string
  book_intro: string
  cover_url: string
  url: string
  catalog: string[]
  rating: IbookRating
  cover_base64: string
  buy_date: Date
  buy_pos: string
  real_price: number
  publishDate: string
}

export interface IbookRating {
  count: number
  value: number
  percent: [number]
}

export interface IbookParams {
  count: number
  value: string
}

export interface IDataType<T = any> {
  response: string
  code: number
  data: T
}

export interface DoubanAPI {
  success: boolean
  data: any
  is_cache: boolean
  message: object
}
