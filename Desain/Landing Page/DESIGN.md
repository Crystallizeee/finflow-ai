---
name: Kinetic Logic
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#444748'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#747878'
  outline-variant: '#c4c7c7'
  surface-tint: '#5f5e5e'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#1c1b1b'
  on-primary-container: '#858383'
  inverse-primary: '#c8c6c5'
  secondary: '#0058be'
  on-secondary: '#ffffff'
  secondary-container: '#2170e4'
  on-secondary-container: '#fefcff'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#2a1700'
  on-tertiary-container: '#b87500'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e5e2e1'
  primary-fixed-dim: '#c8c6c5'
  on-primary-fixed: '#1c1b1b'
  on-primary-fixed-variant: '#474746'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#adc6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#004395'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  display-lg:
    fontFamily: Hanken Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Hanken Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.04em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  container-max: 1280px
  gutter: 24px
  margin-desktop: 40px
  margin-tablet: 24px
  margin-mobile: 16px
---

## Brand & Style

This design system is built for high-performance enterprise environments where clarity, speed, and precision are paramount. The brand personality is authoritative yet approachable, blending a **Modern Corporate** foundation with **Minimalist** efficiency. It targets professionals in data-dense fields—finance, logistics, and SaaS—who require a UI that facilitates deep work without visual fatigue.

The aesthetic prioritizes a "reduced" interface: heavy use of purposeful whitespace, a disciplined color application, and a focus on high-quality typography. The emotional response should be one of quiet confidence and unwavering reliability. By stripping away ornamental clutter, the design system allows the user's data to take center stage, using subtle motion and structural alignment to guide the eye.

## Colors

The palette is grounded in a "High-Contrast Minimal" logic. The primary color is a deep, near-black neutral used for core branding and primary actions, ensuring maximum legibility and impact. The secondary color is a vibrant blue, reserved strictly for interactive affordances, links, and progress indicators. 

A tertiary amber is used sparingly as a "functional" accent to draw attention to warnings or high-priority states without breaking the professional tone. The background is a crisp, off-white neutral that reduces screen glare during long sessions. System states follow standard conventions: Success (Emerald), Error (Rose), and Info (Sky), but are desaturated to maintain the system's sophisticated restraint.

## Typography

This design system utilizes a functional typographic trio. **Hanken Grotesk** is used for headlines, providing a sharp, contemporary edge that feels engineered and precise. **Inter** handles the bulk of body copy and interface elements, chosen for its exceptional legibility in complex layouts. For technical data, status badges, and code snippets, **JetBrains Mono** introduces a monospaced rhythm that aids in rapid scanning of alphanumeric strings.

Hierarchy is established primarily through weight and scale rather than color. Mobile-specific variants are defined for large display roles to ensure content remains readable and balanced on smaller viewports.

## Layout & Spacing

The layout follows a **Fixed-Fluid Hybrid** model. Content is contained within a maximum width of 1280px for desktop viewing to maintain optimal line lengths, while margins and gutters remain fluid on smaller devices. We employ an 8px baseline grid to ensure mathematical harmony across all components.

- **Desktop (1024px+):** 12-column grid, 24px gutters, 40px external margins.
- **Tablet (768px - 1023px):** 8-column grid, 20px gutters, 24px external margins.
- **Mobile (Up to 767px):** 4-column grid, 16px gutters, 16px external margins.

Vertical spacing should leverage increments of 8px (e.g., 16, 24, 32, 48, 64) to create a clear sense of grouping and information density.

## Elevation & Depth

This design system avoids heavy shadows in favor of **Tonal Layers** and **Low-Contrast Outlines**. Depth is communicated through subtle shifts in surface color:

1.  **Level 0 (Base):** The main background using the neutral color.
2.  **Level 1 (Surface):** Cards and main containers, slightly elevated using a 1px border (#E5E7EB) or a pure white fill.
3.  **Level 2 (Overlay):** Modals and dropdowns. These use a soft, highly diffused ambient shadow (0px 10px 25px rgba(0,0,0,0.05)) and a 1px border to separate them from the Level 1 surfaces.

This "Flat-Plus" approach ensures the UI feels light and modern while providing enough depth cues for the user to understand the interface hierarchy.

## Shapes

The shape language is **Soft (0.25rem)**. This subtle rounding strikes a balance between the clinical rigidity of sharp corners and the overly casual nature of fully rounded elements. It conveys a professional, systematic character while remaining approachable.

- **Standard (Buttons, Inputs):** 0.25rem (4px)
- **Large (Cards, Modals):** 0.5rem (8px)
- **Extra Large (Feature Blocks):** 0.75rem (12px)

Icons should follow a 2px stroke weight with slight corner rounding to match the UI's geometric DNA.

## Components

### Buttons
Primary buttons use the Primary Color (Dark) with white text. Secondary buttons use a transparent background with a 1px border. Hover states are indicated by a subtle shift in background opacity (90%) or a slight lift.

### Input Fields
Inputs feature a 1px neutral border that transitions to the Secondary Color (Blue) on focus. Labels use the `label-md` mono font for a technical, precise feel. Error states are indicated by a 2px bottom-border highlight in Rose.

### Chips & Badges
Badges use the monospaced font in all-caps. They are rendered with a "Soft Fill" (10% opacity of the status color) and a 1px border of the same hue, ensuring they stand out without overwhelming the text.

### Cards
Cards are the primary container for data. They should have no shadow by default, instead using a 1px light gray border. In data-heavy views, cards should be "flush," sharing borders to minimize whitespace gaps and maximize information density.

### Data Tables
Tables are a core component. They use a zebra-stripe pattern with a very faint neutral-50 tint on even rows. Headers use `label-sm` with a bottom-border for separation. Interaction on rows is indicated by a light blue highlight.