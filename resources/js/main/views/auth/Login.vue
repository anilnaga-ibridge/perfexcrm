<template>
  <div class="login-container flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <!-- Logo and Header -->
      <div class="flex flex-col items-center justify-center text-center">
        <img :src="resolvedLogo" alt="iBRIDGE Logo" class="login-logo" />
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
            label="Password"
            name="password"
            :rules="[{ required: true, message: 'Please input your password!' }]"
          >
            <a-input-password
              v-model:value="loginForm.password"
              placeholder="Password"
              size="large"
              class="rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
          </a-form-item>

          <!-- Remember Me + Forgot Password -->
          <div class="flex items-center justify-between mb-6">
            <a-checkbox v-model:checked="loginForm.remember">Remember me</a-checkbox>
            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot Password?</a>
          </div>

          <!-- Submit Button -->
          <a-form-item class="mb-0">
            <a-button
              type="primary"
              html-type="submit"
              size="large"
              :loading="loading"
              block
              class="w-full text-white rounded-md font-semibold h-11 login-btn"
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
import { defineComponent, reactive, ref, computed } from 'vue';
import logoUrl from '../../assets/logo.png';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/authStore';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'Login',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();

    const resolvedLogo = computed(() => {
      if (logoUrl && logoUrl.startsWith('/')) {
        const basePath = window.config?.path?.replace(/\/$/, '') || '';
        return basePath + logoUrl;
      }
      return logoUrl;
    });

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
      resolvedLogo,
    };
  },
});
</script>

<style scoped>
.login-logo { height: 48px; object-fit: contain; margin-bottom: 8px; }
.login-btn {
  background: linear-gradient(135deg, #d35400, #7e1e8e, #0b579f) !important;
  border: none !important;
  box-shadow: 0 2px 8px rgba(211,84,0,0.25);
}
.login-btn:hover { opacity: 0.9; box-shadow: 0 4px 14px rgba(211,84,0,0.35); transform: translateY(-1px); }
.login-btn:active { opacity: 0.85; transform: translateY(0); }
.login-container {
  font-family: 'Inter', sans-serif;
}
:deep(.ant-form-item-label > label) {
  font-weight: 500;
  color: #374151;
}
</style>
