export interface IRecord {
  book_id: number
  start_time: string
  time_length: number
  start_page: number
  end_page: number
}

export interface IDataType<T = any> {
  response: string
  code: number
  data: T
}
