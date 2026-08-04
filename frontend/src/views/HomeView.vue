<script setup>
import { ref, onMounted } from 'vue';
import { authService } from '../services/authService';
import { useRouter } from 'vue-router';

const router = useRouter();
const users = ref([]);

onMounted(async () => {
  try {
    const response = await fetch('http://localhost:8000/api/test-user');
    users.value = await response.json();
  } catch (error) {
    console.error('Error fetching users:', error);
  }
});

const handleLogout = async () => {
  await authService.logout();
  router.push('/login');
};
</script>

<template>
  <h1>User List</h1>
  <button @click="handleLogout">Logout</button>
  <ul>
    <li v-for="user in users" :key="user.name">{{ user.name }}</li>
  </ul>
</template>
