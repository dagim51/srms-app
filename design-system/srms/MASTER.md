# Design System Master File — Ethiopian Real Estate Marketplace (SRMS)

> **LOGIC:** When building or refining a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** SRMS (Ethiopian Real Estate Marketplace, Short-Stay & Property Platform)  
**Status:** Unified Single Source of Truth  
**Technology Stack:** Laravel 12 + Blade + Tailwind CSS v4 + Alpine.js  
**Coordinated Skills:** UI/UX Pro Max (Design Systems/Hierarchy) + Taste Skill (Visual Polish/Composition) + Emil Kowalski (Motion Engineering/States)

---

## 1. Global Color Palette

The project uses an intentional warm earth and sun palette rooted in Ethiopian trust, sunlit architecture, and premium modern real estate.

| Role | Light Hex | Dark Hex | CSS Variable / Token |
|------|-----------|----------|----------------------|
| **Brand Primary (500)** | `#F97316` | `#EA580C` | `--brand`, `--color-primary-500` |
| **Brand Primary Hover (600)** | `#EA580C` | `#C2410C` | `--brand-dark`, `--color-primary-600` |
| **Brand Light (400)** | `#FB923C` | `#F97316` | `--brand-light`, `--color-primary-400` |
| **Brand Soft / Tint (50)** | `#FFF7ED` | `#1E1510` | `--brand-soft`, `--color-primary-50` |
| **Primary Text (Foreground)** | `#0F172A` | `#F9FAFB` | `--color-foreground`, `--color-dark-text` |
| **Muted Text** | `#475569` | `#94A3B8` | `--color-muted-foreground`, `--color-dark-subtle` |
| **Border / Divider** | `#EBDBC9` | `#334155` | `--border-warm`, `--color-surface-border`, `--color-dark-border` |
| **Page Background** | `#FFF5EB` | `#0F172A` | `--bg-main`, `--color-page-bg`, `--color-dark-bg` |
| **Subtle Background** | `#FFEFE0` | `#243042` | `--bg-subtle`, `--color-page-subtle`, `--color-dark-elevated` |
| **Surface / Card Background** | `#FFFFFF` | `#1E293B` | `--surface`, `--color-surface`, `--color-dark-surface` |
| **Elevated Surface** | `#FFFFFF` | `#283548` | `--surface`, `--color-dark-elevated` |
| **Success / Verified** | `#16A34A` | `#22C55E` | `--color-success` |
| **Warning** | `#F59E0B` | `#FBBF24` | `--color-warning` |
| **Destructive / Error** | `#DC2626` | `#EF4444` | `--color-destructive`, `--color-error` |
| **Information / Accent** | `#0284C7` | `#38BDF8` | `--color-info` |

---

## 2. Typography System

The typography pairs a high-clarity modern display font with an ultra-legible neutral body font:

- **Display & Headings:** `Plus Jakarta Sans`, system-ui, -apple-system, sans-serif
- **Body & Controls:** `Inter`, system-ui, -apple-system, sans-serif
- **Amharic / Bilingual Display:** System Ethiopic (`Nyala`, `Kefa`, `Abyssinica SIL`), sans-serif

```css
@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
    --font-display: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
}
```

### Type Scale Rules
- **Display 1 (Hero Title):** `text-4xl md:text-5xl lg:text-6xl`, tracking `-0.025em`, font-weight `800`
- **Page Titles:** `text-2xl md:text-3xl`, tracking `-0.02em`, font-weight `700`
- **Section Headers:** `text-lg md:text-xl`, font-weight `600`
- **Body Regular:** `text-sm md:text-base`, line-height `1.6`, font-weight `400`
- **Metadata / Badges / Captions:** `text-xs font-semibold tracking-wider uppercase`

---

## 3. Motion & Micro-Interaction Tokens (Emil Kowalski Spec)

Motion must be restrained, physics-calibrated, and purposeful. Perpetual animations and distracting floating loops are strictly prohibited.

```css
:root {
    --ease-float:      cubic-bezier(0.23, 1, 0.32, 1);
    --ease-out:        cubic-bezier(0.23, 1, 0.32, 1);
    --ease-in-out:     cubic-bezier(0.77, 0, 0.175, 1);
    --ease-drawer:     cubic-bezier(0.32, 0.72, 0, 1);
    --duration-fast:   140ms;
    --duration-btn:    160ms;
    --duration-normal: 200ms;
    --duration-card:   220ms;
    --duration-modal:  220ms;
}
```

### Interaction Rules
1. **Button Presses:** Must feel tactile with active compression (`transform: scale(0.97)` on `:active`).
2. **Card Hover:** Subtle elevation only (`transform: translateY(-2px)` with `--ease-out` over `220ms`). Never scale cards up so they collide with neighbors.
3. **Dropdowns & Drawers:** Scale out from trigger (`scale(0.96) -> scale(1)`), never translate across entire screen without context.
4. **Accessibility:** Strictly enforce `@media (prefers-reduced-motion: reduce)` with instantaneous transitions (`0.01ms`).

---

## 4. Anti-AI Design Guidelines (Eliminating Generic Patterns)

To prevent the application from feeling like an AI template:

1. **No Universal Card-Wrapping:** Do not wrap every heading, metric, and paragraph in identical cards with identical borders and shadows. Use whitespace and typography to group content naturally.
2. **No Horizontal Clipping on Mobile:** Never use `white-space: nowrap` on long badges or tags without responsive abbreviations or flexible flex wrapping.
3. **No Collision with Floating Elements:** Sticky/floating navbars must have adequate clearance. Content must not scroll under transparent unpadded bars without a backdrop scrim.
4. **High Contrast Form Controls:** Placeholder text and inactive inputs in dark mode must meet WCAG 4.5:1 contrast ratio against `#111827` surface background.
5. **No Emojis as Icons:** All iconography must be consistent Heroicons SVG glyphs with semantic accessible labels.

---

## 5. Component Specifications

### Buttons
- **Primary:** `bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl px-4 py-2.5 transition active:scale-97`
- **Secondary:** `bg-transparent border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-xl px-4 py-2.5 transition active:scale-97`
- **Ghost:** `hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg px-3 py-1.5 transition`

### Inputs & Selects
- Light: `bg-white border-gray-300 text-gray-900 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 rounded-xl`
- Dark: `bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/25 rounded-xl`

### Badges & Status Indicators
- Verified: `bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20`
- For Sale: `bg-orange-500 text-white font-semibold`
- For Rent: `bg-blue-600 text-white font-semibold`
- Development: `bg-purple-600 text-white font-semibold`
