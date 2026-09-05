<template>
  <div
    class="gc"
    :style="{ perspective: perspective + 'px' }"
    @mouseenter="onEnter"
    @mouseleave="onLeave"
    @mousemove="onMove"
    ref="root"
  >
    <!-- Layer 1: Shadow — floats below, stretches, opposite to cursor -->
    <div class="gc-shadow" ref="elShadow"></div>

    <!-- Layer 2: Glow — ambient blob behind card -->
    <div class="gc-glow" ref="elGlow"></div>

    <!-- Layer 3: Image — 40px behind glass, moves MORE than everything -->
    <div class="gc-image-layer" ref="elImageLayer">
      <img :src="image" class="gc-image" />
    </div>

    <!-- Layer 4: Glass body -->
    <div class="gc-glass" ref="elGlass">

      <!-- Specular: bright spot tracks cursor -->
      <div class="gc-specular" ref="elSpecular"></div>

      <!-- Reflection: radial, follows cursor, bends near edges -->
      <div class="gc-reflection" ref="elReflection"></div>

      <!-- Border layers -->
      <div class="gc-border-inner"></div>
      <div class="gc-border-outer"></div>
      <div class="gc-border-specular" ref="elBorderSpec"></div>

      <!-- Corner lighting -->
      <div class="gc-corner gc-corner--tl"></div>
      <div class="gc-corner gc-corner--br"></div>

      <!-- Noise -->
      <div class="gc-noise"></div>

      <!-- Content: text layer, moves less than glass -->
      <div class="gc-content" ref="elContent">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted, onBeforeUnmount } from 'vue'

// ── Perlin noise ──
const P = new Uint8Array(512)
;(function () {
  const p = Array.from({ length: 256 }, (_, i) => i)
  for (let i = 255; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[p[i], p[j]] = [p[j], p[i]]
  }
  for (let i = 0; i < 512; i++) P[i] = p[i & 255]
})()
function fade(t) { return t * t * t * (t * (t * 6 - 15) + 10) }
function lerp(a, b, t) { return a + t * (b - a) }
function perlin2(x, y) {
  const X = Math.floor(x) & 255, Y = Math.floor(y) & 255
  const xf = x - Math.floor(x), yf = y - Math.floor(y)
  const u = fade(xf), v = fade(yf)
  const aa = P[P[X] + Y], ab = P[P[X] + Y + 1]
  const ba = P[P[X + 1] + Y], bb = P[P[X + 1] + Y + 1]
  const g = (h, dx, dy) => { const m = h & 3; return (m < 2 ? dx : -dx) + (m === 0 || m === 3 ? dy : -dy) }
  return lerp(lerp(g(aa, xf, yf), g(ba, xf - 1, yf), u), lerp(g(ab, xf, yf - 1), g(bb, xf - 1, yf - 1), u), v)
}

// ── Spring with configurable weight ──
function spring(stiffness, damping) {
  return { v: 0, t: 0, vel: 0,
    step(dt) {
      this.vel += (-stiffness * (this.v - this.t)) * dt
      this.vel *= Math.max(0, 1 - damping * dt)
      this.v += this.vel * dt
    }
  }
}

export default defineComponent({
  name: 'GlassCard',
  props: {
    image: String,
    title: String,
    index: { type: Number, default: 0 },
    perspective: { type: Number, default: 1000 },
    floatSpeed: { type: Number, default: 0.12 },
    floatAmp: { type: Number, default: 6 },
    maxTilt: { type: Number, default: 14 },
  },
  setup(props) {
    const root = ref(null)
    const elGlass = ref(null)
    const elShadow = ref(null)
    const elGlow = ref(null)
    const elImageLayer = ref(null)
    const elContent = ref(null)
    const elReflection = ref(null)
    const elSpecular = ref(null)
    const elBorderSpec = ref(null)

    let raf = null
    let t = props.index * 47.3
    let hovering = false
    let enteredAt = 0
    let mx = 0, my = 0
    let cw = 0, ch = 0
    let entered = false

    // Springs — VISUAL weight, not engineering numbers
    // Glass: moderate follow
    const sRX = spring(45, 6.5)
    const sRY = spring(45, 6.5)
    const sTX = spring(38, 5.5)
    const sTY = spring(38, 5.5)
    // Lift: heavy, slow rise
    const sLift = spring(30, 5)
    const sScale = spring(40, 6)
    // Image: SLOWEST, most lag — feels 40px behind glass
    const sIX = spring(22, 4)
    const sIY = spring(22, 4)
    // Reflection: FAST, follows light
    const sRX2 = spring(60, 8)
    const sRY2 = spring(60, 8)
    // Glow
    const sGlow = spring(28, 5)

    // Idle springs (driven by Perlin)
    const iRX = spring(15, 3)
    const iRY = spring(15, 3)
    const iTX = spring(12, 3)
    const iTY = spring(12, 3)

    function tick() {
      const dt = 1 / 60
      t += dt

      if (hovering) {
        const nx = (mx - cw / 2) / (cw / 2)
        const ny = (my - ch / 2) / (ch / 2)

        sRX.t = -ny * props.maxTilt
        sRY.t = nx * props.maxTilt
        sTX.t = nx * 12
        sTY.t = ny * 12
        sLift.t = 1
        sScale.t = 1

        // Image moves INVERTED and MORE — the key visual
        sIX.t = nx * -50
        sIY.t = ny * -50

        // Reflection follows cursor position
        sRX2.t = nx * 60
        sRY2.t = ny * 60

        sGlow.t = 1
      } else {
        // Organic idle
        const n1 = perlin2(t * props.floatSpeed, props.index * 7.1)
        const n2 = perlin2(t * props.floatSpeed * 0.7, props.index * 7.1 + 100)
        const n3 = perlin2(t * props.floatSpeed * 1.2, props.index * 7.1 + 200)
        const n4 = perlin2(t * props.floatSpeed * 0.9, props.index * 7.1 + 300)

        iRX.t = n1 * 1.5
        iRY.t = n2 * 1.5
        iTX.t = n3 * props.floatAmp * 0.4
        iTY.t = n4 * props.floatAmp

        sLift.t = 0
        sScale.t = 0
        sIX.t = 0
        sIY.t = 0
        sRX2.t = 0
        sRY2.t = 0
        sGlow.t = 0
      }

      // Step springs
      sRX.step(dt); sRY.step(dt); sTX.step(dt); sTY.step(dt)
      sLift.step(dt); sScale.step(dt)
      sIX.step(dt); sIY.step(dt)
      sRX2.step(dt); sRY2.step(dt)
      sGlow.step(dt)
      iRX.step(dt); iRY.step(dt); iTX.step(dt); iTY.step(dt)

      apply()
      raf = requestAnimationFrame(tick)
    }

    function apply() {
      const rx = sRX.v + iRX.v
      const ry = sRY.v + iRY.v
      const tx = sTX.v + iTX.v
      const ty = sTY.v + iTY.v
      const lv = sLift.v * -35
      const sc = 1 + sScale.v * 0.04

      // Glass: the main rotating surface
      const glass = elGlass.value
      if (glass) {
        glass.style.transform =
          `translate3d(${tx}px, ${ty + lv}px, 0) ` +
          `rotateX(${rx}deg) rotateY(${ry}deg) ` +
          `scale(${sc})`
      }

      // Shadow: floating, stretches with tilt
      const shadow = elShadow.value
      if (shadow) {
        const sx = -ry * 3
        const sy = Math.abs(rx) * 2 + 18 + lv * -0.25
        const stretch = 1 + Math.abs(rx) * 0.02 + Math.abs(ry) * 0.02
        const blur = 40 + Math.abs(rx) * 3 + Math.abs(ry) * 3
        const opacity = 0.18 + sGlow.v * 0.1
        shadow.style.transform =
          `translate3d(${sx}px, ${sy}px, -50px) ` +
          `scaleY(${stretch}) scaleX(${1 + Math.abs(ry) * 0.01})`
        shadow.style.filter = `blur(${blur}px)`
        shadow.style.opacity = opacity
      }

      // Glow: ambient blob
      const glow = elGlow.value
      if (glow) {
        const gx = -ry * 5
        const gy = rx * 5
        glow.style.transform =
          `translate3d(${gx}px, ${gy}px, -60px) ` +
          `scale(${1 + sGlow.v * 0.25})`
        glow.style.opacity = 0.1 + sGlow.v * 0.35
      }

      // Image: SEPARATE from glass, moves MORE (the key visual difference)
      const imgLayer = elImageLayer.value
      if (imgLayer) {
        const ix = sIX.v + tx * 0.3
        const iy = sIY.v + ty * 0.3 + lv * 0.2
        const isc = 1.1 + sGlow.v * 0.08
        imgLayer.style.transform =
          `translate3d(${ix}px, ${iy}px, -40px) ` +
          `rotateX(${rx * 0.6}deg) rotateY(${ry * 0.6}deg) ` +
          `scale(${isc})`
      }

      // Content: moves LESS than glass
      const content = elContent.value
      if (content) {
        const cx = tx * -0.2
        const cy = ty * -0.2 + lv * 0.06
        content.style.transform = `translate3d(${cx}px, ${cy}px, 20px)`
      }

      // Reflection: radial, follows cursor
      const reflec = elReflection.value
      if (reflec) {
        const lx = mx + sRX2.v * 0.4
        const ly = my + sRY2.v * 0.4
        const intensity = 0.15 + sGlow.v * 0.55
        reflec.style.background =
          `radial-gradient(ellipse 55% 50% at ${lx}px ${ly}px, ` +
          `rgba(255,255,255,${intensity}), ` +
          `rgba(255,255,255,${intensity * 0.4}) 35%, ` +
          `transparent 65%)`
      }

      // Specular: tight bright spot
      const spec = elSpecular.value
      if (spec) {
        const px = ((mx / cw) * 100).toFixed(1)
        const py = ((my / ch) * 100).toFixed(1)
        const b = 0.25 + sGlow.v * 0.35
        spec.style.background =
          `radial-gradient(circle at ${px}% ${py}%, ` +
          `rgba(255,255,255,${b}), transparent 45%)`
        spec.style.opacity = hovering ? 1 : 0.15
      }

      // Border specular: conic
      const bspec = elBorderSpec.value
      if (bspec) {
        const angle = Math.atan2(my - ch / 2, mx - cw / 2) * (180 / Math.PI)
        const i = 0.08 + sGlow.v * 0.22
        bspec.style.background =
          `conic-gradient(from ${angle}deg, ` +
          `transparent 0%, rgba(255,255,255,${i}) 10%, ` +
          `transparent 20%, rgba(159,142,214,${i * 0.4}) 45%, ` +
          `transparent 55%, rgba(255,255,255,${i}) 80%, transparent 100%)`
        bspec.style.opacity = hovering ? 1 : 0.15
      }
    }

    function onEnter(e) {
      hovering = true
      entered = true
      enteredAt = performance.now()
      measure()
      onMove(e)
    }

    function onLeave() {
      hovering = false
    }

    function onMove(e) {
      if (!root.value) return
      const r = root.value.getBoundingClientRect()
      mx = e.clientX - r.left
      my = e.clientY - r.top
    }

    function measure() {
      if (!root.value) return
      const r = root.value.getBoundingClientRect()
      cw = r.width
      ch = r.height
    }

    onMounted(() => {
      measure()
      raf = requestAnimationFrame(tick)
      window.addEventListener('resize', measure)
    })

    onBeforeUnmount(() => {
      cancelAnimationFrame(raf)
      window.removeEventListener('resize', measure)
    })

    return {
      root, elGlass, elShadow, elGlow, elImageLayer, elContent,
      elReflection, elSpecular, elBorderSpec,
      onEnter, onLeave, onMove
    }
  }
})
</script>

<style scoped>
.gc {
  position: relative;
  width: 100%;
  height: 380px;
  transform-style: preserve-3d;
  cursor: pointer;
}

/* ── Shadow: floating, stretches, opposite to cursor ── */
.gc-shadow {
  position: absolute;
  inset: 3%;
  border-radius: 24px;
  background: radial-gradient(ellipse at 50% 50%, rgba(30, 15, 60, 0.5) 0%, rgba(30, 15, 60, 0.2) 45%, transparent 70%);
  z-index: 0;
  will-change: transform, filter, opacity;
  pointer-events: none;
}

/* ── Glow ── */
.gc-glow {
  position: absolute;
  inset: -20%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(159, 142, 214, 0.4) 0%, rgba(159, 142, 214, 0.1) 45%, transparent 70%);
  filter: blur(40px);
  z-index: 0;
  will-change: transform, opacity;
  pointer-events: none;
}

/* ── Image layer: 40px behind glass, INDEPENDENT ── */
.gc-image-layer {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 20px;
  overflow: hidden;
  z-index: 1;
  will-change: transform;
  pointer-events: none;
}

.gc-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  pointer-events: none;
}

/* ── Glass body ── */
.gc-glass {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 20px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(20px) saturate(180%) brightness(112%);
  -webkit-backdrop-filter: blur(20px) saturate(180%) brightness(112%);
  z-index: 2;
  will-change: transform;
  transform-style: preserve-3d;

  box-shadow:
    inset 0 2px 0 rgba(255, 255, 255, 0.3),
    inset 0 -2px 0 rgba(0, 0, 0, 0.05),
    inset 8px 0 30px rgba(255, 255, 255, 0.08),
    inset -8px 0 30px rgba(0, 0, 0, 0.03);
}

/* ── Specular ── */
.gc-specular {
  position: absolute;
  inset: 0;
  border-radius: 20px;
  z-index: 10;
  pointer-events: none;
  mix-blend-mode: overlay;
  opacity: 0.15;
  will-change: opacity;
  transition: opacity 0.4s ease;
}

/* ── Reflection ── */
.gc-reflection {
  position: absolute;
  inset: -40%;
  border-radius: 50%;
  z-index: 11;
  pointer-events: none;
}

/* ── Border inner ── */
.gc-border-inner {
  position: absolute;
  inset: 0;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  pointer-events: none;
  z-index: 15;
}

/* ── Border outer ── */
.gc-border-outer {
  position: absolute;
  inset: -1px;
  border-radius: 21px;
  border: 1px solid rgba(255, 255, 255, 0.06);
  pointer-events: none;
  z-index: 14;
}

/* ── Border specular ── */
.gc-border-specular {
  position: absolute;
  inset: -1px;
  border-radius: 21px;
  z-index: 16;
  pointer-events: none;
  opacity: 0.15;
  mix-blend-mode: overlay;
  will-change: opacity;
  transition: opacity 0.5s ease;
}

/* ── Corner lighting ── */
.gc-corner {
  position: absolute;
  width: 55%;
  height: 55%;
  z-index: 13;
  pointer-events: none;
  border-radius: 20px;
}
.gc-corner--tl {
  top: 0; left: 0;
  background: radial-gradient(circle at 0% 0%, rgba(255,255,255,0.15) 0%, transparent 45%);
}
.gc-corner--br {
  bottom: 0; right: 0;
  background: radial-gradient(circle at 100% 100%, rgba(0,0,0,0.08) 0%, transparent 45%);
}

/* ── Noise ── */
.gc-noise {
  position: absolute;
  inset: 0;
  border-radius: 20px;
  z-index: 12;
  pointer-events: none;
  opacity: 0.025;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
  background-size: 128px 128px;
}

/* ── Content ── */
.gc-content {
  position: relative;
  z-index: 20;
  padding: 16px 22px 22px;
  margin-top: 44%;
  pointer-events: auto;
  will-change: transform;
}
</style>
