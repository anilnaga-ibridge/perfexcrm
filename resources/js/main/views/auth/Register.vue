<template>
  <div class="register-container flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <div class="flex flex-col items-center justify-center text-center">
        <img :src="resolvedLogo" alt="Logo" class="register-logo" />
        <h2 class="mt-6 text-2xl font-bold tracking-tight text-slate-900">Create Account</h2>
        <p class="mt-2 text-sm text-slate-500">Register to get started</p>
      </div>
      <div class="bg-white p-8 border border-slate-200 rounded-lg shadow-sm">
        <a-form layout="vertical" :model="form" @finish="handleRegister">
          <a-form-item label="Full Name" name="name"
            :rules="[{ required: true, message: 'Please input your name!' }]">
            <a-input v-model:value="form.name" placeholder="John Doe" size="large" />
          </a-form-item>
          <a-form-item label="Email Address" name="email"
            :rules="[{ required: true, type: 'email', message: 'Please input a valid email!' }]">
            <a-input v-model:value="form.email" placeholder="john@example.com" size="large" />
          </a-form-item>
          <a-form-item label="Password" name="password"
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
            <a-button type="primary" html-type="submit" size="large" :loading="loading" block class="register-btn">
              Register
            </a-button>
          </a-form-item>
        </a-form>
        <div class="mt-4 text-center text-sm text-slate-500">
          Already have an account?
          <router-link :to="{ name: 'admin.login' }" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</router-link>
        </div>
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
  name: 'Register',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();

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
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    });

    const handleRegister = async () => {
      loading.value = true;
      const result = await authStore.registerAction({ ...form });
      if (result.success) {
        message.success('Account created successfully!');
        router.push({ name: 'admin.dashboard' });
      } else {
        message.error(result.message);
      }
      loading.value = false;
    };

    return { form, loading, handleRegister, resolvedLogo };
  },
});
</script>

<style scoped>
.register-logo { height: 48px; object-fit: contain; margin-bottom: 8px; }
.register-btn {
  background: linear-gradient(135deg, #d35400, #7e1e8e, #0b579f) !important;
  border: none !important;
  box-shadow: 0 2px 8px rgba(211,84,0,0.25);
}
.register-btn:hover { opacity: 0.9; box-shadow: 0 4px 14px rgba(211,84,0,0.35); transform: translateY(-1px); }
.register-btn:active { opacity: 0.85; transform: translateY(0); }
.register-container { font-family: 'Inter', sans-serif; }
:deep(.ant-form-item-label > label) { font-weight: 500; color: #374151; }
</style>
