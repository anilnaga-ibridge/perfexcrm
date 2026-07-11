<template>
  <div class="forgot-container flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <div class="flex flex-col items-center justify-center text-center">
        <img :src="resolvedLogo" alt="Logo" class="forgot-logo" />
        <h2 class="mt-6 text-2xl font-bold tracking-tight text-slate-900">Forgot Password</h2>
        <p class="mt-2 text-sm text-slate-500">Enter your email and we'll send you a reset link</p>
      </div>
      <div class="bg-white p-8 border border-slate-200 rounded-lg shadow-sm">
        <a-form v-if="!sent" layout="vertical" :model="form" @finish="handleForgot">
          <a-form-item label="Email Address" name="email"
            :rules="[{ required: true, type: 'email', message: 'Please input a valid email!' }]">
            <a-input v-model:value="form.email" placeholder="your@email.com" size="large" />
          </a-form-item>
          <a-form-item class="mb-0">
            <a-button type="primary" html-type="submit" size="large" :loading="loading" block class="forgot-btn">
              Send Reset Link
            </a-button>
          </a-form-item>
        </a-form>
        <div v-else class="text-center py-4">
          <p class="text-green-600 font-medium">Reset link sent!</p>
          <p class="text-sm text-slate-500 mt-1">Check your email for the password reset link.</p>
        </div>
        <div class="mt-4 text-center text-sm text-slate-500">
          <router-link :to="{ name: 'admin.login' }" class="font-medium text-indigo-600 hover:text-indigo-500">Back to login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref, computed } from 'vue';
import logoUrl from '../../assets/logo.png';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ForgotPassword',
  setup() {
    const resolvedLogo = computed(() => {
      if (logoUrl && logoUrl.startsWith('/')) {
        const basePath = window.config?.path?.replace(/\/$/, '') || '';
        const parsedBase = basePath.replace(/^https?:\/\/[^\/]+/, '');
        if (parsedBase && logoUrl.startsWith(parsedBase)) {
          return logoUrl;
        }
        return basePath + logoUrl;
      }
      return logoUrl;
    });

    const loading = ref(false);
    const sent = ref(false);
    const form = reactive({ email: '' });

    const handleForgot = async () => {
      loading.value = true;
      try {
        await axios.post('/auth/forgot-password', { email: form.email });
        sent.value = true;
      } catch (err) {
        message.error(err.response?.data?.message || 'Failed to send reset link.');
      } finally {
        loading.value = false;
      }
    };

    return { form, loading, sent, handleForgot, resolvedLogo };
  },
});
</script>

<style scoped>
.forgot-logo { height: 48px; object-fit: contain; margin-bottom: 8px; }
.forgot-btn {
  background: linear-gradient(135deg, #d35400, #7e1e8e, #0b579f) !important;
  border: none !important;
}
.forgot-btn:hover { opacity: 0.9; }
.forgot-container { font-family: 'Inter', sans-serif; }
:deep(.ant-form-item-label > label) { font-weight: 500; color: #374151; }
</style>
