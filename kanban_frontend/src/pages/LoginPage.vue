<template>
  <q-page class="flex flex-center">
    <q-card style="width: 300px">
      <q-card-section>
        <div class="text-h6">Login</div>
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input v-model="email" label="Email" filled />
        <q-input v-model="password" label="Password" type="password" filled />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn label="Login" color="primary" @click="handleLogin" :loading="loading" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { api } from 'boot/axios';
import axios from 'axios';
import { useRouter } from 'vue-router';

const email = ref('');
const password = ref('');
const loading = ref(false);
const router = useRouter();

const handleLogin = async () => {
  loading.value = true;
  try {
    // 1. Get CSRF Cookie (Must be done before login)
    // We use the raw axios instance here to hit the root domain, not /api
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    });

    // Manual fix: Extract XSRF-TOKEN and set it in headers
    // This ensures the header is sent even if Axios auto-detection fails
    const tokenMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (tokenMatch && tokenMatch[1]) {
      api.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(tokenMatch[1]);
    }

    // 2. Attempt Login
    await api.post('/login', {
      email: email.value,
      password: password.value
    });

    // 3. Redirect on success
    await router.push('/');
  } catch (error: any) {
    console.error('Login failed:', error);
    if (error.response && error.response.status === 419) {
      alert('CSRF Error (419). Please clear browser cookies and check SESSION_DRIVER/SESSION_SECURE_COOKIE in backend .env.');
    } else {
      alert('Login failed. Check console for details.');
    }
  } finally {
    loading.value = false;
  }
};
</script>
