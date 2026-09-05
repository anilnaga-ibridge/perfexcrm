<template>
  <div class="register-container flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <!-- Claymorphic Background Decorators -->
    <div class="clay-bg-decor cloud-1"></div>
    <div class="clay-bg-decor cloud-2"></div>
    
    <!-- Left Plant -->
    <div class="clay-bg-decor plant-left">
      <div class="leaf leaf-1"></div>
      <div class="leaf leaf-2"></div>
      <div class="leaf leaf-3"></div>
      <div class="pot"></div>
    </div>
    
    <!-- Right Flower -->
    <div class="clay-bg-decor flower-right">
      <div class="flower-stem"></div>
      <div class="flower-petal petal-1"></div>
      <div class="flower-petal petal-2"></div>
      <div class="flower-petal petal-3"></div>
      <div class="flower-petal petal-4"></div>
      <div class="flower-petal petal-5"></div>
      <div class="flower-center"></div>
      <div class="pot"></div>
    </div>

    <!-- Main Two-Column Wrapper -->
    <div class="register-wrapper">
      
      <!-- Left Column: Holographic Robot Mascot (Visible on Desktop) -->
      <div class="robot-display-column">
        <div class="robot-visual-wrapper">
          <!-- Ambient Scanner Orbits styled with theme colors -->
          <div class="scanner-orbit scanner-orbit-1"></div>
          <div class="scanner-orbit scanner-orbit-2"></div>
          
          <!-- Robot Image Container -->
          <div class="robot-mascot-container">
            <img 
              :src="robotHologramUrl" 
              alt="Robotic Hologram" 
              class="robot-mascot-img"
            />
          </div>
        </div>
        
        <!-- Status Badge -->
        <div class="robot-status">
          <div class="status-pulse"></div>
          <span class="status-text">SYSTEM REGISTRATION ONLINE</span>
        </div>
      </div>

      <!-- Right Column: Phone-like Clay Card -->
      <div class="phone-card">
        <div class="phone-header">
          <div class="heart-container">
            <span class="heart-icon">❤️</span>
          </div>
          <h2 class="welcome-title">
            <span class="sparkle sparkle-left">✨</span>
            Create Account
            <span class="sparkle sparkle-right">✨</span>
          </h2>
          <p class="welcome-subtitle">Register to get started</p>
        </div>

        <!-- Avatar Character Container (Clay Image Avatar) -->
        <div class="avatar-container">
          <img :src="clayAvatarRegisterUrl" alt="Register Avatar" class="clay-avatar-img" />
        </div>

        <!-- Form Card Overlap -->
        <div class="form-card">
          <a-form
            layout="vertical"
            :model="form"
            @finish="handleRegister"
            class="register-form-element"
          >
            <!-- Full Name Field -->
            <a-form-item
              name="name"
              :rules="[{ required: true, message: 'Please input your name!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </template>
                <a-input
                  v-model:value="form.name"
                  placeholder="Full Name"
                >
                  <template #suffix>
                    <span class="input-dummy-suffix"></span>
                  </template>
                </a-input>
              </glass-input>
            </a-form-item>

            <!-- Email Field -->
            <a-form-item
              name="email"
              :rules="[{ required: true, type: 'email', message: 'Please input a valid email!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                </template>
                <a-input
                  v-model:value="form.email"
                  placeholder="Email Address"
                >
                  <template #suffix>
                    <span class="input-dummy-suffix"></span>
                  </template>
                </a-input>
              </glass-input>
            </a-form-item>

            <!-- Password Field -->
            <a-form-item
              name="password"
              :rules="[{ required: true, min: 8, message: 'Password must be at least 8 characters!' }]"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </template>
                <a-input-password
                  v-model:value="form.password"
                  placeholder="Password"
                />
              </glass-input>
            </a-form-item>

            <!-- Confirm Password Field -->
            <a-form-item
              name="password_confirmation"
              :rules="confirmPasswordRules"
              class="clay-form-item"
            >
              <glass-input>
                <template #icon>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                  </svg>
                </template>
                <a-input-password
                  v-model:value="form.password_confirmation"
                  placeholder="Confirm Password"
                />
              </glass-input>
            </a-form-item>

            <!-- Submit Button -->
            <a-form-item class="mb-0">
              <a-button
                type="primary"
                html-type="submit"
                size="large"
                :loading="loading"
                block
                class="register-btn-main"
              >
                Register
              </a-button>
            </a-form-item>
          </a-form>

          <div class="text-center text-sm text-slate-500 font-medium mt-6">
            Already have an account?
            <router-link :to="{ name: 'admin.login' }" class="login-link-redirect font-bold">Sign in</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref, computed } from 'vue';
import logoUrl from '../../assets/logo.png';
import robotHologramUrl from '../../assets/robot_hologram.png';
import clayAvatarUrl from '../../assets/clay_avatar.png';
import clayAvatarRegisterUrl from '../../assets/clay_avatar_register.svg';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/authStore';
import { message } from 'ant-design-vue';
import GlassInput from '../../components/GlassInput.vue';

export default defineComponent({
  name: 'Register',
  components: { GlassInput },
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

    const confirmPasswordRules = [
      { required: true, message: 'Please confirm your password!' },
      {
        validator: async (_rule, value) => {
          if (value && value !== form.password) {
            return Promise.reject('Passwords do not match!');
          }
          return Promise.resolve();
        },
      },
    ];

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

    return { form, loading, handleRegister, confirmPasswordRules, resolvedLogo, robotHologramUrl, clayAvatarRegisterUrl };
  },
});
</script>

<style scoped>
/* ── Claymorphic UI Theme & Background ───────────────────────── */
.register-container {
  background: #bcb3e2; /* Soft pastel purple background */
  position: relative;
  overflow: hidden;
  font-family: 'Outfit', 'Inter', sans-serif;
}

/* Background Puffy Clouds */
.clay-bg-decor {
  position: absolute;
  pointer-events: none;
}

.cloud-1 {
  top: 15%;
  left: 10%;
  width: 140px;
  height: 60px;
  background: #faf6f0;
  border-radius: 100px;
  box-shadow: 
    inset 6px 6px 12px rgba(255,255,255,0.9),
    5px 10px 20px rgba(70, 50, 110, 0.18);
  opacity: 0.9;
}
.cloud-1::before {
  content: '';
  position: absolute;
  top: -30px;
  left: 20px;
  width: 60px;
  height: 60px;
  background: #faf6f0;
  border-radius: 50%;
  box-shadow: inset 4px 4px 8px rgba(255,255,255,0.9);
}
.cloud-1::after {
  content: '';
  position: absolute;
  top: -20px;
  right: 25px;
  width: 50px;
  height: 50px;
  background: #faf6f0;
  border-radius: 50%;
  box-shadow: inset 4px 4px 8px rgba(255,255,255,0.9);
}

.cloud-2 {
  top: 25%;
  right: 8%;
  width: 120px;
  height: 50px;
  background: #faf6f0;
  border-radius: 100px;
  box-shadow: 
    inset 6px 6px 12px rgba(255,255,255,0.9),
    5px 10px 20px rgba(70, 50, 110, 0.18);
  opacity: 0.85;
}
.cloud-2::before {
  content: '';
  position: absolute;
  top: -25px;
  left: 20px;
  width: 50px;
  height: 50px;
  background: #faf6f0;
  border-radius: 50%;
  box-shadow: inset 4px 4px 8px rgba(255,255,255,0.9);
}

/* Left Plant */
.plant-left {
  bottom: 8%;
  left: 6%;
  width: 120px;
  height: 220px;
}
@media (max-width: 992px) {
  .plant-left, .cloud-1, .cloud-2, .flower-right, .robot-display-column {
    display: none !important;
  }
}
.pot {
  position: absolute;
  bottom: 0;
  left: 30px;
  width: 60px;
  height: 50px;
  background: #e59a7d; /* Terracotta pot */
  border-radius: 8px 8px 20px 20px;
  box-shadow: 
    inset 3px 3px 6px rgba(255,255,255,0.5),
    inset -3px -3px 6px rgba(0,0,0,0.15),
    3px 6px 12px rgba(70, 50, 110, 0.2);
}
.pot::before {
  content: '';
  position: absolute;
  top: -4px;
  left: -4px;
  width: 68px;
  height: 8px;
  background: #e59a7d;
  border-radius: 4px;
  box-shadow: 
    inset 2px 2px 4px rgba(255,255,255,0.5),
    1px 2px 4px rgba(0,0,0,0.1);
}
.leaf {
  position: absolute;
  background: #88a37b; /* Clay green */
  border-radius: 50% 50% 10px 50%;
  box-shadow: 
    inset 3px 3px 6px rgba(255,255,255,0.4),
    2px 4px 8px rgba(0,0,0,0.1);
}
.leaf-1 {
  width: 40px;
  height: 60px;
  bottom: 50px;
  left: 20px;
  transform: rotate(-35deg);
}
.leaf-2 {
  width: 45px;
  height: 65px;
  bottom: 85px;
  left: 55px;
  transform: scaleX(-1) rotate(-35deg);
}
.leaf-3 {
  width: 35px;
  height: 55px;
  bottom: 130px;
  left: 30px;
  transform: rotate(-15deg);
}

/* Right Flower */
.flower-right {
  bottom: 8%;
  right: 6%;
  width: 120px;
  height: 220px;
}
.flower-stem {
  position: absolute;
  bottom: 45px;
  left: 58px;
  width: 6px;
  height: 100px;
  background: #88a37b;
  border-radius: 4px;
  box-shadow: inset 1px 1px 3px rgba(255,255,255,0.4);
}
.flower-center {
  position: absolute;
  bottom: 125px;
  left: 48px;
  width: 26px;
  height: 26px;
  background: #ecd278; /* yellow center */
  border-radius: 50%;
  z-index: 10;
  box-shadow: 
    inset 2px 2px 5px rgba(255,255,255,0.6),
    1px 2px 4px rgba(0,0,0,0.15);
}
.flower-petal {
  position: absolute;
  width: 30px;
  height: 30px;
  background: #e8a7b0; /* soft pink petals */
  border-radius: 50%;
  box-shadow: 
    inset 2px 2px 5px rgba(255,255,255,0.4),
    1px 2px 4px rgba(0,0,0,0.1);
}
.petal-1 { bottom: 138px; left: 46px; }
.petal-2 { bottom: 123px; left: 63px; }
.petal-3 { bottom: 108px; left: 52px; }
.petal-4 { bottom: 114px; left: 33px; }
.petal-5 { bottom: 132px; left: 30px; }

/* ── Two-Column Layout ───────────────────────────────────────────── */
.register-wrapper {
  display: flex;
  align-items: center;
  gap: 70px;
  position: relative;
  z-index: 10;
  max-width: 1000px;
  width: 100%;
  justify-content: center;
}

/* Left Column: Robot Mascot Display */
.robot-display-column {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
}

.robot-visual-wrapper {
  position: relative;
  width: 360px;
  height: 360px;
  display: flex;
  align-items: center;
  justify-content: center;
  perspective: 1000px;
}

/* Mascot container with soft clay border mapping to theme */
.robot-mascot-container {
  width: 300px;
  height: 300px;
  border-radius: 50%;
  background: radial-gradient(circle, #2d244f 30%, #150f2a 100%);
  border: 4px solid #eae6f3;
  box-shadow: 
    12px 12px 28px rgba(70, 50, 110, 0.3),
    inset 4px 4px 8px rgba(255, 255, 255, 0.15);
  transform-style: preserve-3d;
  animation: float-mascot 5s ease-in-out infinite alternate;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

.robot-mascot-img {
  width: 96%;
  height: 96%;
  object-fit: cover;
  border-radius: 50%;
  mix-blend-mode: screen; /* Filters out the black background */
  z-index: 5;
  animation: mascot-spin 15s linear infinite;
  transform-style: preserve-3d;
}

@keyframes float-mascot {
  0% { transform: translateY(0px); }
  100% { transform: translateY(-12px); }
}

@keyframes mascot-spin {
  from { transform: rotateY(0deg); }
  to { transform: rotateY(360deg); }
}

.scanner-orbit {
  position: absolute;
  border-radius: 50%;
  border: 1.5px dashed rgba(188, 179, 226, 0.4);
  pointer-events: none;
}
.scanner-orbit-1 {
  width: 330px;
  height: 330px;
  animation: spin-clockwise 25s linear infinite;
}
.scanner-orbit-2 {
  width: 360px;
  height: 360px;
  border: 1px dotted rgba(188, 179, 226, 0.25);
  animation: spin-counter 35s linear infinite;
}

@keyframes spin-clockwise {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
@keyframes spin-counter {
  from { transform: rotate(360deg); }
  to { transform: rotate(0deg); }
}

.robot-status {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  padding: 6px 16px;
  background: #faf6f0;
  border: 1.5px solid rgba(188, 179, 226, 0.4);
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(70, 50, 110, 0.08);
}
.status-pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #9f8ed6;
  animation: status-glow 1.5s ease-in-out infinite alternate;
}
@keyframes status-glow {
  0% { transform: scale(0.9); opacity: 0.5; box-shadow: 0 0 0 0px rgba(159, 142, 214, 0.5); }
  100% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 8px 3px rgba(159, 142, 214, 0.3); }
}
.status-text {
  font-family: monospace;
  font-size: 11px;
  color: #5f4f8d;
  letter-spacing: 0.1em;
  font-weight: 700;
}

/* ── Phone Card ────────────────────────────────────────────────── */
.phone-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  background: #faf6f0;
  border: 2px solid rgba(255, 255, 255, 0.6);
  border-radius: 44px;
  padding: 30px 20px 20px 20px;
  box-shadow: 
    0 30px 60px rgba(60, 50, 100, 0.25),
    inset 4px 4px 10px rgba(255, 255, 255, 0.85);
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Phone Header */
.phone-header {
  text-align: center;
  margin-bottom: 5px;
}
.heart-container {
  display: flex;
  justify-content: center;
  margin-bottom: 2px;
  animation: beat 1.2s infinite alternate;
}
@keyframes beat {
  to { transform: scale(1.15); }
}
.heart-icon {
  font-size: 24px;
}
.welcome-title {
  font-size: 28px;
  font-weight: 800;
  color: #5f4f8d;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.welcome-subtitle {
  font-size: 13.5px;
  color: #8c7fb2;
  font-weight: 600;
  margin-top: 2px;
}
.sparkle {
  font-size: 16px;
  color: #ecd278;
}

/* ── Clay Character Avatar ──────────────────────────────────────── */
.avatar-container {
  position: relative;
  width: 100%;
  height: 185px;
  display: flex;
  justify-content: center;
  margin-top: 15px;
  margin-bottom: -50px;
  z-index: 15;
  overflow: visible;
  pointer-events: none;
}

.clay-avatar-img {
  width: 220px;
  height: 220px;
  object-fit: contain;
  transform: translateY(-35px);
  filter: drop-shadow(0 10px 20px rgba(60, 50, 100, 0.15));
  mix-blend-mode: multiply; /* Blends out the white background perfectly */
}

/* ── Overlapping Form Card ────────────────────────────────────── */
.form-card {
  position: relative;
  z-index: 20;
  width: 100%;
  background: #faf6f0;
  border-radius: 36px;
  padding: 30px 18px 18px 18px;
  box-shadow: 
    0 15px 30px rgba(60, 50, 100, 0.12),
    inset 3px 3px 8px rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.clay-form-item {
  margin-bottom: 18px !important;
}

/* Links & Buttons */
.login-link-redirect {
  color: #8c7fb2;
  font-size: 13.5px;
  font-weight: 700;
  transition: all 0.2s;
}
.login-link-redirect:hover {
  color: #9f8ed6;
}

.register-btn-main {
  background: #9f8ed6 !important; /* clay purple */
  border: none !important;
  border-radius: 999px !important;
  height: 48px !important;
  font-weight: 800 !important;
  font-size: 16px !important;
  color: #ffffff !important;
  box-shadow: 
    0 8px 16px rgba(159, 142, 214, 0.35),
    inset 2px 2px 4px rgba(255, 255, 255, 0.4) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.register-btn-main:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 10px 20px rgba(159, 142, 214, 0.45),
    inset 2px 2px 4px rgba(255, 255, 255, 0.4) !important;
  filter: brightness(1.03);
}

.register-btn-main:active {
  transform: translateY(1px);
  box-shadow: 
    inset 3px 3px 6px rgba(0, 0, 0, 0.15) !important;
}
</style>
