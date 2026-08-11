import { describe, it, expect, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import CreatePostModal from '../components/CreatePostModal.vue'

const post = {
  id: 1,
  caption: 'Test caption',
  image_url: 'posts/test.jpg',
  user: { id: 1, username: 'tester' }
}

afterEach(() => {
  document.body.innerHTML = ''
})

const mountModal = () => mount(CreatePostModal, {
  props: { show: false, post }
})

describe('CreatePostModal', () => {
  it('shows current image in edit mode when opened', async () => {
    const wrapper = mountModal()
    await wrapper.setProps({ show: true })
    await flushPromises()
    expect(document.querySelector('.preview-area')).toBeTruthy()
    expect(document.querySelector('.upload-area')).toBeNull()
  })

  it('clicking Change in edit mode reveals the upload area', async () => {
    const wrapper = mountModal()
    await wrapper.setProps({ show: true })
    await flushPromises()
    document.querySelector('.change-btn').click()
    await flushPromises()
    expect(document.querySelector('.preview-area')).toBeNull()
    expect(document.querySelector('.upload-area')).toBeTruthy()
  })
})