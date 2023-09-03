import mitt, { Emitter } from 'mitt'

type Events = {
  [propName: string]: any
}
const mittBus: Emitter<Events> = mitt<Events>()
export default mittBus
