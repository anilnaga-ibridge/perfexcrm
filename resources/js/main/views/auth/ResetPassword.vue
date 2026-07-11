<template>
  <div class="reset-container flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <div class="flex flex-col items-center justify-center text-center">
        <img :src="resolvedLogo" alt="Logo" class="reset-logo" />
        <h2 class="mt-6 text-2xl font-bold tracking-tight text-slate-900">Reset Password</h2>
        <p class="mt-2 text-sm text-slate-500">Enter your new password</p>
      </div>
      <div class="bg-white p-8 border border-slate-200 rounded-lg shadow-sm">
        <a-form layout="vertical" :model="form" @finish="handleReset">
          <a-form-item label="Email Address" name="email"
            :rules="[{ required: true, type: 'email', message: 'Please input a valid email!' }]">
            <a-input v-model:value="form.email" placeholder="your@email.com" size="large" />
          </a-form-item>
          <a-form-item label="New Password" name="password"
            :rules="[{ required: true, min: 8, message: 'Password must be at least 8 characters!' }]">
            <a-input-password v-model:value="form.password" placeholder="Min. 8 characters" size="large" />
          </a-form-item>
          <a-form-item label="Confirm Password" name="password_confirmation"
            :rules="[
              { required: true, message: 'Please confirm your password!' },
              { validator: (_, value) => value === form.password ? Promise.resolve() : Promise.reject('Passwords do not match!') }
            ]">
            <a-input-password v-model:value="form.password_confirmation" placeholder="Repeat password" size="large" />
          </a-form-item>
          <a-form-item class="mb-0">
            <a-button type="primary" html-type="submit" size="large" :loading="loading" block class="reset-btn">
              Reset Password
            </a-button>
          </a-form-item>
        </a-form>
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
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ResetPassword',
  setup() {
    const route = useRoute();
    const router = useRouter();

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
    const form = reactive({
      email: route.query.email || '',
      password: '',
      password_confirmation: '',
      token: route.params.token || '',
    });

    const handleReset = async () => {
      loading.value = true;
      try {
        await axios.post('/auth/reset-password', { ...form });
        message.success('Password reset successfully!');
        router.push({ name: 'admin.login' });
      } catch (err) {
        message.error(err.response?.data?.message || 'Failed to reset password.');
      } finally {
        loading.value = false;
      }
    };

    return { form, loading, handleReset, resolvedLogo };
  },
});
</script>

<style scoped>
.reset-logo { height: 48px; object-fit: contain; margin-bottom: 8px; }
.reset-btn {
  background: linear-gradient(135deg, #d35400, #7e1e8e, #0b579f) !important;
  border: none !important;
}
.reset-btn:hover { opacity: 0.9; }
.reset-container { font-family: 'Inter', sans-serif; }
:deep(.ant-form-item-label > label) { font-weight: 500; color: #374151; }
</style>
