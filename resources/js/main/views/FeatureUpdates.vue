<template>
  <div class="feature-updates-page">
    <div class="page-header">
      <h1 class="page-title">Feature Updates</h1>
      <p class="page-subtitle">Move your cursor over any card</p>
    </div>

    <div class="cards-grid">
      <GlassCard
        v-for="(card, i) in cards"
        :key="i"
        :image="card.image"
        :title="card.title"
        :index="i"
        :floatSpeed="card.floatSpeed"
        :floatAmp="card.floatAmp"
        :maxTilt="card.maxTilt"
      >
        <div class="card-badge">{{ card.badge }}</div>
        <h3 class="card-title">{{ card.title }}</h3>
        <p class="card-desc">{{ card.desc }}</p>
      </GlassCard>
    </div>

    <div class="timeline-section">
      <h2 class="section-heading">What You See</h2>
      <div class="timeline-list">
        <div v-for="(item, i) in items" :key="i" class="timeline-item">
          <div class="tl-num">{{ i + 1 }}</div>
          <div class="tl-body">
            <h4 class="tl-title">{{ item.title }}</h4>
            <p class="tl-desc">{{ item.desc }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import GlassCard from '../components/GlassCard.vue'

export default defineComponent({
  name: 'FeatureUpdates',
  components: { GlassCard },
  setup() {
    const cards = [
      {
        title: 'Suspended Glass',
        desc: 'Heavy lag. Card follows cursor like a physical object hanging in air.',
        badge: 'Motion',
        image: 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=400&h=260&fit=crop',
        floatSpeed: 0.12, floatAmp: 6, maxTilt: 14
      },
      {
        title: 'Light Reflection',
        desc: 'Radial highlight follows cursor. Bends near edges. Corners catch light.',
        badge: 'Light',
        image: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400&h=260&fit=crop',
        floatSpeed: 0.15, floatAmp: 5, maxTilt: 12
      },
      {
        title: 'Depth Layers',
        desc: 'Glass moves 10px. Image moves 28px. Reflection 40px. Shadow opposite.',
        badge: 'Parallax',
        image: 'https://images.unsplash.com/photo-1557683316-973673baf926?w=400&h=260&fit=crop',
        floatSpeed: 0.1, floatAmp: 7, maxTilt: 13
      },
      {
        title: 'Elastic Return',
        desc: 'Overshoots. Bounces once. Settles like a hanging object.',
        badge: 'Physics',
        image: 'https://images.unsplash.com/photo-1579546929518-9e396f3cc809?w=400&h=260&fit=crop',
        floatSpeed: 0.18, floatAmp: 5, maxTilt: 11
      },
      {
        title: 'Floating Shadow',
        desc: 'Shadow stretches with tilt. Compresses when flat. Moves opposite to cursor.',
        badge: 'Shadow',
        image: 'https://images.unsplash.com/photo-1557682250-33bd709cbe85?w=400&h=260&fit=crop',
        floatSpeed: 0.14, floatAmp: 8, maxTilt: 15
      },
      {
        title: 'Crystal Border',
        desc: 'Inner highlight. Outer glow. Specular edge. Corner lighting. Not a CSS line.',
        badge: 'Refraction',
        image: 'https://images.unsplash.com/photo-1614850523459-c2f4c699c52e?w=400&h=260&fit=crop',
        floatSpeed: 0.16, floatAmp: 6, maxTilt: 12
      },
    ]

    const items = [
      { title: 'Heavy Lag', desc: 'Card follows cursor with weight. Not instant. Feels suspended.' },
      { title: 'Radial Reflection', desc: 'Light moves across glass surface. Bends near edges. Corners catch light.' },
      { title: 'Layered Depth', desc: 'Glass, image, reflection, shadow, glow — all at different speeds.' },
      { title: 'Floating Shadow', desc: 'Stretches with tilt. Softens when lifted. Hardens when flat.' },
      { title: 'Organic Idle', desc: 'Cards never stop. Subtle, alive floating like objects in air.' },
      { title: 'Gradual Hover', desc: 'Lift, reflection, glass bend, glow, image drift — all finish together.' },
      { title: 'Elastic Return', desc: 'Overshoots, bounces once, settles. Like a hanging object.' },
      { title: 'Crystal Border', desc: 'Multiple highlight layers. Not a CSS border.' },
    ]

    return { cards, items }
  }
})
</script>

<style scoped>
.feature-updates-page {
  min-height: 100vh;
  padding: 10px 0 60px;
  background:
    radial-gradient(ellipse 80% 50% at 20% 30%, rgba(159, 142, 214, 0.1) 0%, transparent 60%),
    radial-gradient(ellipse 60% 40% at 80% 70%, rgba(232, 167, 176, 0.08) 0%, transparent 55%);
}

.page-header {
  text-align: center;
  margin-bottom: 48px;
}
.page-title {
  font-size: 28px;
  font-weight: 800;
  color: var(--theme-text-dark, #5f4f8d);
  margin: 0 0 8px;
}
.page-subtitle {
  font-size: 15px;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.45;
  margin: 0;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 50px;
  max-width: 1100px;
  margin: 0 auto 80px;
  padding: 0 20px;
}
@media (max-width: 900px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .cards-grid { grid-template-columns: 1fr; max-width: 400px; } }

.card-badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--theme-primary, #9f8ed6);
  background: rgba(159, 142, 214, 0.12);
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 8px;
}
.card-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--theme-text-dark, #5f4f8d);
  margin: 0 0 6px;
}
.card-desc {
  font-size: 12.5px;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.55;
  margin: 0;
  line-height: 1.5;
}

.timeline-section {
  max-width: 700px;
  margin: 0 auto;
  padding: 0 20px;
}
.section-heading {
  font-size: 22px;
  font-weight: 800;
  color: var(--theme-text-dark, #5f4f8d);
  text-align: center;
  margin-bottom: 32px;
}
.timeline-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 14px 20px;
  background: rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  transition: transform 0.2s ease;
}
.timeline-item:hover {
  transform: translateY(-1px);
}
.tl-num {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--theme-primary, #9f8ed6);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  border-radius: 8px;
}
.tl-body { flex: 1; }
.tl-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--theme-text-dark, #5f4f8d);
  margin: 0 0 3px;
}
.tl-desc {
  font-size: 12px;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.5;
  margin: 0;
  line-height: 1.4;
}
</style>
