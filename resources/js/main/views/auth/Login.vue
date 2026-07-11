<template>
  <div class="login-container flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <!-- Ambient Holographic Glows -->
    <div class="ambient-glow cyan-glow"></div>
    <div class="ambient-glow amber-glow"></div>
    <div class="grid-overlay"></div>

    <div class="login-wrapper">
      <!-- Left Column: Robotic Hologram Display (Visible on Desktop) -->
      <div class="hologram-display">
        <div class="hologram-visual-wrapper">
          <!-- Ambient circular scanner orbits -->
          <div class="scanner-orbit scanner-orbit-1"></div>
          <div class="scanner-orbit scanner-orbit-2"></div>
          
          <!-- The Robotic Hologram Image (With 3D Hover tilt & rotation classes) -->
          <div class="robot-hologram-container">
            <img 
              :src="robotHologramUrl" 
              alt="Robotic Hologram" 
              class="robot-hologram-img"
            />
          </div>
        </div>
        <div class="hologram-status">
          <div class="status-pulse"></div>
          <span class="status-text">AI ASSISTANT LINK ACTIVE</span>
        </div>
      </div>

      <!-- Right Column: Login Card -->
      <div class="login-card space-y-8">
        <!-- Logo and Header -->
        <div class="flex flex-col items-center justify-center text-center header-section">
          <div class="hologram-wrapper">
            <div class="hologram-ring hologram-ring-1"></div>
            <div class="hologram-ring hologram-ring-2"></div>
            <div class="logo-orb">
              <img :src="resolvedLogo" alt="iBRIDGE Logo" class="login-logo" />
            </div>
          </div>
          <h2 class="mt-5 text-2xl font-bold tracking-tight text-slate-800 glow-text">SYSTEM ACCESS</h2>
          <p class="mt-2 text-xs text-cyan-600 font-mono tracking-widest uppercase">SECURE CLIENT PORTAL v2.8</p>
        </div>

        <!-- Login Form Inside Glass Card -->
        <a-form
          layout="vertical"
          :model="loginForm"
          @finish="handleLogin"
          class="login-form-element"
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
            />
          </a-form-item>

          <!-- Remember Me + Forgot Password -->
          <div class="flex items-center justify-between mb-6">
            <a-checkbox v-model:checked="loginForm.remember">Remember me</a-checkbox>
            <router-link :to="{ name: 'admin.forgot-password' }" class="forgot-link">Forgot Password?</router-link>
          </div>

          <!-- Submit Button -->
          <a-form-item class="mb-0">
            <a-button
              type="primary"
              html-type="submit"
              size="large"
              :loading="loading"
              block
              class="login-btn"
            >
              ESTABLISH CONNECTION
            </a-button>
          </a-form-item>
        </a-form>
        
        <div class="text-center text-sm text-slate-500 font-medium">
          Don't have credentials?
          <router-link :to="{ name: 'admin.register' }" class="register-link">Register Device</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref, computed } from 'vue';
import logoUrl from '../../assets/logo.png';
import robotHologramUrl from '../../assets/robot_hologram.png';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/authStore';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'Login',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();

    const resolvedLogo = computed(() => logoUrl);

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
      robotHologramUrl,
    };
  },
});
</script>

<style scoped>
/* ── Ambient Glows & Background (Light Theme) ───────────────────────── */
.login-container {
  background: 
    radial-gradient(ellipse at 15% 15%, rgba(99, 102, 241, 0.12) 0%, transparent 45%),
    radial-gradient(ellipse at 75% 75%, rgba(236, 72, 153, 0.09) 0%, transparent 45%),
    radial-gradient(ellipse at 55% 5%, rgba(6, 182, 212, 0.08) 0%, transparent 40%),
    linear-gradient(135deg, #eef2ff 0%, #f1f5f9 40%, #fdf2f8 70%, #ecfeff 100%);
  position: relative;
  overflow: hidden;
  font-family: 'Inter', sans-serif;
}

.ambient-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  opacity: 0.28;
}
.cyan-glow {
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(0, 243, 255, 0.25) 0%, rgba(0, 243, 255, 0) 70%);
  top: 15%;
  left: 10%;
  animation: pulse-cyan 8s ease-in-out infinite alternate;
}
.amber-glow {
  width: 420px;
  height: 420px;
  background: radial-gradient(circle, rgba(230, 126, 34, 0.12) 0%, rgba(230, 126, 34, 0) 70%);
  bottom: 10%;
  right: 12%;
  animation: pulse-amber 10s ease-in-out infinite alternate;
}
@keyframes pulse-cyan {
  0% { transform: scale(1); opacity: 0.2; }
  100% { transform: scale(1.15); opacity: 0.35; }
}
@keyframes pulse-amber {
  0% { transform: scale(1); opacity: 0.1; }
  100% { transform: scale(1.2); opacity: 0.22; }
}

.grid-overlay {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.015) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 0, 0, 0.015) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
  z-index: 1;
}

/* ── Two-Column Layout ───────────────────────────────────────────── */
.login-wrapper {
  display: flex;
  align-items: center;
  gap: 60px;
  position: relative;
  z-index: 10;
  max-width: 1000px;
  width: 100%;
  justify-content: center;
}

/* Hologram display column */
.hologram-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
}

.hologram-visual-wrapper {
  position: relative;
  width: 380px;
  height: 380px;
  display: flex;
  align-items: center;
  justify-content: center;
  perspective: 1000px; /* Enable 3D space */
}

/* Container to handle 3D perspective and rotation */
.robot-hologram-container {
  width: 320px;
  height: 320px;
  border-radius: 50%;
  background: radial-gradient(circle, #0d1527 30%, #060914 100%);
  border: 2px solid rgba(0, 243, 255, 0.4);
  box-shadow: 
    0 15px 35px rgba(0, 0, 0, 0.15),
    0 0 25px rgba(0, 243, 255, 0.25),
    inset 0 0 20px rgba(0, 243, 255, 0.15);
  transform-style: preserve-3d;
  animation: float-y 5s ease-in-out infinite alternate;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

.robot-hologram-img {
  width: 96%;
  height: 96%;
  object-fit: cover;
  border-radius: 50%;
  mix-blend-mode: screen; /* Filters out the pure black background! */
  z-index: 5;
  animation: hologram-spin 12s linear infinite;
  transform-style: preserve-3d;
}

@keyframes float-y {
  0% { transform: translateY(0px); }
  100% { transform: translateY(-12px); }
}

@keyframes hologram-spin {
  from { transform: rotateY(0deg); }
  to { transform: rotateY(360deg); }
}

.scanner-orbit {
  position: absolute;
  border-radius: 50%;
  border: 1.5px dashed rgba(0, 162, 255, 0.25);
  pointer-events: none;
}
.scanner-orbit-1 {
  width: 350px;
  height: 350px;
  animation: spin-clockwise 25s linear infinite;
}
.scanner-orbit-2 {
  width: 380px;
  height: 380px;
  border: 1px dotted rgba(0, 162, 255, 0.12);
  animation: spin-counter 35s linear infinite;
}

.hologram-status {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  padding: 6px 16px;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(0, 162, 255, 0.2);
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.status-pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #00829f;
  animation: status-glow 1.5s ease-in-out infinite alternate;
}
@keyframes status-glow {
  0% { transform: scale(0.9); opacity: 0.5; box-shadow: 0 0 0 0px rgba(0, 243, 255, 0.5); }
  100% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 8px 3px rgba(0, 243, 255, 0.3); }
}
.status-text {
  font-family: monospace;
  font-size: 11px;
  color: #00829f;
  letter-spacing: 0.1em;
  font-weight: 700;
}

/* Hide hologram on mobile views */
@media (max-width: 768px) {
  .login-wrapper {
    flex-direction: column;
    gap: 0;
  }
  .hologram-display {
    display: none;
  }
}

/* ── Hologram Logo Orb ───────────────────────────────────────────── */
.header-section {
  position: relative;
  z-index: 5;
}
.hologram-wrapper {
  position: relative;
  width: 90px;
  height: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}
.logo-orb {
  width: 68px;
  height: 68px;
  background: rgba(255, 255, 255, 0.6);
  border: 1.5px solid rgba(0, 243, 255, 0.4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  box-shadow: 
    0 0 20px rgba(0, 243, 255, 0.12),
    inset 0 0 12px rgba(255, 255, 255, 0.6);
  overflow: hidden;
  padding: 8px;
}
.login-logo {
  height: 100%;
  width: 100%;
  object-fit: contain;
  filter: drop-shadow(0 0 3px rgba(0, 243, 255, 0.25));
}
.hologram-ring {
  position: absolute;
  border-radius: 50%;
  border: 1px dashed rgba(0, 162, 255, 0.35);
  pointer-events: none;
}
.hologram-ring-1 {
  width: 80px;
  height: 80px;
  animation: spin-clockwise 10s linear infinite;
}
.hologram-ring-2 {
  width: 92px;
  height: 92px;
  border: 1.5px dotted rgba(0, 162, 255, 0.15);
  animation: spin-counter 15s linear infinite;
}
@keyframes spin-clockwise {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
@keyframes spin-counter {
  from { transform: rotate(360deg); }
  to { transform: rotate(0deg); }
}

.glow-text {
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.9);
  letter-spacing: 0.05em;
}

/* ── Light Glassmorphic Login Card ──────────────────────────────────── */
.login-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  background: rgba(255, 255, 255, 0.55);
  border: 1.5px solid rgba(255, 255, 255, 0.75);
  border-top: 2.5px solid rgba(0, 243, 255, 0.5);
  border-radius: 24px;
  padding: 42px 36px;
  backdrop-filter: blur(35px);
  -webkit-backdrop-filter: blur(35px);
  box-shadow: 
    0 20px 48px rgba(0, 0, 0, 0.06),
    inset 0 3px 6px rgba(255, 255, 255, 0.95);
}

.login-form-element {
  position: relative;
  z-index: 5;
}

/* ── Ant-Design Component Overrides (Light Mode) ─────────────────── */
:deep(.ant-form-item-label > label) {
  color: #334155 !important;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.02em;
}

:deep(.ant-input),
:deep(.ant-input-password) {
  background: rgba(255, 255, 255, 0.6) !important;
  border: 1.5px solid rgba(0, 0, 0, 0.08) !important;
  border-radius: 12px !important;
  color: #0f172a !important;
  padding: 10px 14px !important;
  transition: all 0.25s !important;
}

:deep(.ant-input-affix-wrapper) {
  background: rgba(255, 255, 255, 0.6) !important;
  border: 1.5px solid rgba(0, 0, 0, 0.08) !important;
  border-radius: 12px !important;
  padding: 0 14px !important;
  transition: all 0.25s !important;
}
:deep(.ant-input-affix-wrapper .ant-input) {
  background: transparent !important;
  border: none !important;
  padding: 10px 0 !important;
}

:deep(.ant-input:focus),
:deep(.ant-input-affix-wrapper-focused),
:deep(.ant-input-affix-wrapper:focus) {
  border-color: #00bada !important;
  box-shadow: 0 0 10px rgba(0, 243, 255, 0.15) !important;
  background: rgba(255, 255, 255, 0.9) !important;
}

:deep(.ant-input-password-icon) {
  color: rgba(0, 0, 0, 0.4) !important;
}
:deep(.ant-input-password-icon:hover) {
  color: #00bada !important;
}

:deep(.ant-input::placeholder) {
  color: #94a3b8 !important;
}

/* Checkbox overrides */
:deep(.ant-checkbox-wrapper) {
  color: #475569 !important;
  font-size: 13.5px;
  font-weight: 500;
}
:deep(.ant-checkbox-inner) {
  background-color: rgba(255, 255, 255, 0.6) !important;
  border-color: rgba(0, 0, 0, 0.15) !important;
  border-radius: 4px !important;
}
:deep(.ant-checkbox-checked .ant-checkbox-inner) {
  background-color: #00bada !important;
  border-color: #00bada !important;
}
:deep(.ant-checkbox-checked::after) {
  border-color: #00bada !important;
}

/* Links & Buttons */
.forgot-link {
  color: #00829f;
  font-size: 13.5px;
  font-weight: 600;
  transition: all 0.2s;
}
.forgot-link:hover {
  color: #0055ff;
}

.register-link {
  color: #00829f;
  font-weight: 700;
  transition: all 0.2s;
}
.register-link:hover {
  color: #0055ff;
}

.login-btn {
  background: linear-gradient(135deg, #00d2f3 0%, #0055ff 100%) !important;
  border: none !important;
  border-radius: 12px !important;
  height: 46px !important;
  font-weight: 700 !important;
  font-size: 14px !important;
  letter-spacing: 0.06em;
  color: #ffffff !important;
  text-shadow: 0 1px 2px rgba(0,0,0,0.15);
  box-shadow: 0 4px 15px rgba(0, 162, 255, 0.22) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  cursor: pointer;
}
.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 8px 24px rgba(0, 162, 255, 0.32),
    0 0 10px rgba(0, 243, 255, 0.2) !important;
  filter: brightness(1.05);
}
.login-btn:active {
  transform: translateY(0);
}
</style>
