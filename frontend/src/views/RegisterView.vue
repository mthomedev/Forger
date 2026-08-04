<script setup>
import { ref } from 'vue';
import { authService } from '../services/authService';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = ref({ name: '', email: '', password: '', password_confirmation: '' });
const error = ref('');

const register = async () => {
  try {
    const data = await authService.register(form.value);
    authService.setToken(data.access_token);
    router.push('/');
  } catch (err) {
  error.value = err.message || 'Registration error';
  }
  };
  </script>

<template>
  <div>
    <h1>Register</h1>
    <form @submit.prevent="register">
      <input v-model="form.name" placeholder="Name" required />
      <input v-model="form.email" type="email" placeholder="Email" required />
      <input v-model="form.password" type="password" placeholder="Password" required />
      <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required />
      <button type="submit">Register</button>
    </form>
    <p v-if="error">{{ error }}</p>
  </div>
</template>
