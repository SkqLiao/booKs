export interface Ibook {
  title: string
  subtitle: string
  original_title: string
  isbn: string
  author: [string]
  translator: [string]
  publish: string
  producer: string
  pages: number
  price: number
  binding: string
  series: string
  book_intro: string
  cover_url: string
  url: string
  catalog: [string]
  rating: IbookRating
  cover_base64: string
  buy_date: Date
  buy_pos: string
  real_price: string
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