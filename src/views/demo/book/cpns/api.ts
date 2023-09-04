import { request, request2 } from '@/utils'

export default {
  getPosts: (params = {}) => request2.get('/get.php', { params }),
  getPostById: (id: string) => request.get(`/post/${id}`),
  addPost: (data: any) => request.post('/post', data),
  updatePost: (data: any) => request2.post(`/read/update.php`, data),
  deletePost: (id: any) => request2.post(`/read/delete.php`, {id : id})
}
