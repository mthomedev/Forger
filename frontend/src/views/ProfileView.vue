<script setup>
import { ref, onMounted } from 'vue';
import { profileService } from '../services/profileService';

const user = ref(null);
const form = ref({ name: '', username: '', bio: '' });
const avatarFile = ref(null);

const fetchProfile = async () => {
  user.value = await profileService.getProfile();
  form.value = { name: user.value.name, username: user.value.username, bio: user.value.bio };
};

const updateProfile = async () => {
  const data = await profileService.updateProfile(form.value);
  user.value = data.user;
};

const handleAvatarUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    const data = await profileService.uploadAvatar(file);
    user.value = data.user;
  }
};

onMounted(fetchProfile);
</script>

<template>
  <div v-if="user">
    <h1>Profile</h1>
    <img v-if="user.avatar_path" :src="'http://localhost:8000/storage/' + user.avatar_path" width="100" />
    <input type="file" @change="handleAvatarUpload" />
    
    <form @submit.prevent="updateProfile">
      <input v-model="form.name" placeholder="Name" />
      <input v-model="form.username" placeholder="Username" />
      <textarea v-model="form.bio" placeholder="Bio"></textarea>
      <button type="submit">Update Profile</button>
    </form>
    
    <p>Posts: {{ user.posts_count }} | Followers: {{ user.followers_count }} | Following: {{ user.following_count }}</p>
  </div>
</template>
