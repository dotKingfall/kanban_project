<template>
  <q-page class="flex flex-center">
    <q-card style="width: 300px">
      <q-card-section>
        <div class="text-h6 text-center">Login</div>
      </q-card-section>

      <q-form @submit="handleLogin" ref="loginForm">
        <q-card-section class="q-gutter-md">

          <!--EMAIL INPUT-->
          <q-input 
            v-model="email" 
            label="Email" 
            filled
            lazy-rules
            :rules="[
              val => !!val || 'Insira seu email',
              val => isValidEmail(val) || 'Insira um email válido'
            ]"
          />

          <!--PASSWORD INPUT-->
          <q-input 
            v-model="password" 
            label="Senha" 
            type="password" 
            filled 
            :rules="[val => !!val || 'Insira sua senha']"
          />
        </q-card-section>

        <q-card-actions vertical class="q-px-md q-pb-md">
          <q-btn 
            label="ENTRAR" 
            type="submit" 
            color="primary" 
            class="full-width" 
            :loading="loading" 
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { api } from 'boot/axios';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'stores/auth';

const email = ref('');
const password = ref('');
const loading = ref(false);
const loginForm = ref<any>(null);
const router = useRouter();
const authStore = useAuthStore();

// Regex for standard email validation
const isValidEmail = (val: string) => {
  const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  return emailPattern.test(val);
};

onMounted(async () => {
  if (localStorage.getItem('is_logged_in') === 'true' && authStore.user) {
    await router.push('/dashboard');
  }
});

const handleLogin = async () => {
  // TRY TO LOG IN AFTER VALIDATION
  const success = await loginForm.value.validate();
  if (!success) return;

  loading.value = true;
  try {
    // 1. Get CSRF Cookie
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    });

    const tokenMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (tokenMatch && tokenMatch[1]) {
      api.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(tokenMatch[1]);
    }

    await authStore.login({ email: email.value, password: password.value });
    await router.push('/dashboard');
  } catch (error) {
    console.error('Login failed:', error);
    alert('Falha no login. Verifique suas credenciais e tente novamente.');
  } finally {
    loading.value = false;
  }
};
</script>