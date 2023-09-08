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
  cover_base64: string
  buy_date: Date
  buy_pos: string
  real_price: number
  publishDate: string
  rating_count: number
  rating_value: number
  rating_percent: number[]
}

export interface IQueryParams {
  table: string
  fields: string[]
  conditions?: string[]
  order_by?: string
  limit?: number
  offset?: number
  group_by?: string
}

export interface IDataType<T = any> {
  message: string
  sql?: string
  error?: string
  code: number
  data: T
}

export interface DoubanAPI {
  success: boolean
  data: any
  is_cache: boolean
  message: object
}

export interface IRecord {
  id: number
  book_id: number
  date: Date
  time_length: number
  start_page: number
  end_page: number
}
