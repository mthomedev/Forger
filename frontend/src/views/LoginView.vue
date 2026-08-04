<script setup>
import { ref } from 'vue';
import { authService } from '../services/authService';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = ref({ email: '', password: '' });
const error = ref('');

const login = async () => {
  try {
    const data = await authService.login(form.value);
    authService.setToken(data.access_token);
    router.push('/');
  } catch (err) {
    error.value = err.message || 'Login error';
  }
};
</script>

<template>
  <div>
    <h1>Login</h1>
    <form @submit.prevent="login">
      <input v-model="form.email" type="email" placeholder="Email" required />
      <input v-model="form.password" type="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>
    <p v-if="error">{{ error }}</p>
  </div>
</template>
