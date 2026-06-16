<template>
  <div class="login-container flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <!-- Logo and Header -->
      <div class="flex flex-col items-center justify-center text-center">
        <div class="flex items-center space-x-2">
          <!-- Perfex Styled Logo Icon -->
          <svg class="h-10 w-10 text-indigo-600" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M25 10H50L25 90H0L25 10Z" fill="#2563EB" />
            <path d="M50 10H75L50 90H25L50 10Z" fill="#E11D48" />
            <path d="M75 10H100L75 90H50L75 10Z" fill="#1E293B" />
          </svg>
          <span class="text-3xl font-extrabold tracking-tight text-slate-900">Perfex</span>
        </div>
        <h2 class="mt-6 text-2xl font-bold tracking-tight text-slate-900">Login</h2>
        <p class="mt-2 text-sm text-slate-500">Welcome, please sign in to your dashboard</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white p-8 border border-slate-200 rounded-lg shadow-sm">
        <a-form
          layout="vertical"
          :model="loginForm"
          @finish="handleLogin"
        >
          <!-- Email Field -->
          <a-form-item
            label="Email Address"
            name="email"
            :rules="[{ required: true, type: 'email', message: 'Please input a valid email address!' }]"
          >
            <a-input
              v-model:value="loginForm.email"
              placeholder="admin@test.com"
              size="large"
              class="rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
          </a-form-item>

          <!-- Password Field -->
          <a-form-item
            name="password"
            :rules="[{ required: true, message: 'Please input your password!' }]"
          >
            <template #label>
              <div class="flex justify-between w-full">
                <span>Password</span>
                <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot Password?</a>
              </div>
            </template>
            <a-input-password
              v-model:value="loginForm.password"
              placeholder="Password"
              size="large"
              class="rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
          </a-form-item>

          <!-- Remember Me -->
          <div class="flex items-center justify-between mb-6">
            <a-checkbox v-model:checked="loginForm.remember">Remember me</a-checkbox>
          </div>

          <!-- Submit Button -->
          <a-form-item class="mb-0">
            <a-button
              type="primary"
              html-type="submit"
              size="large"
              :loading="loading"
              block
              class="w-full bg-slate-900 border-slate-900 hover:bg-slate-800 hover:border-slate-800 focus:bg-slate-800 focus:border-slate-800 text-white rounded-md font-semibold h-11"
            >
              Login
            </a-button>
          </a-form-item>
        </a-form>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/authStore';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'Login',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    
    const loading = ref(false);
    const loginForm = reactive({
      email: 'admin@test.com', // Pre-fill like the demo
      password: 'admin',
      remember: true,
    });

    const handleLogin = async () => {
      loading.value = true;
      try {
        const result = await authStore.loginAction({
          email: loginForm.email,
          password: loginForm.password,
        });

        if (result.success) {
          message.success('Welcome back!');
          router.push({ name: 'admin.dashboard' });
        } else {
          message.error(result.message);
        }
      } catch (err) {
        message.error('An error occurred during login. Please try again.');
      } finally {
        loading.value = false;
      }
    };

    return {
      loginForm,
      loading,
      handleLogin,
    };
  },
});
</script>

<style scoped>
.login-container {
  font-family: 'Inter', sans-serif;
}
:deep(.ant-form-item-label > label) {
  font-weight: 500;
  color: #374151;
}
</style>
