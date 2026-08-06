import api from './api'

export default {
  getFeed(page = 1) {
    return api.get(`/posts?page=${page}`)
  },
  getPost(id) {
    return api.get(`/posts/${id}`)
  },
  getUserPosts(userId, page = 1) {
    return api.get(`/users/${userId}/posts?page=${page}`)
  },
  createPost(formData) {
    return api.postForm('/posts', formData)
  },
  deletePost(id) {
    return api.delete(`/posts/${id}`)
  },
  toggleLike(postId) {
    return api.post(`/posts/${postId}/like`)
  },
  getComments(postId) {
    return api.get(`/posts/${postId}/comments`)
  },
  addComment(postId, body) {
    return api.post(`/posts/${postId}/comments`, { body })
  },
  deleteComment(commentId) {
    return api.delete(`/comments/${commentId}`)
  }
}
