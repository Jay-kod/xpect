# UI_GUIDELINES.md

THEME: orange and black, bright and sharp. Dark theme only in v1.

Colour tokens (use these names, never hard-coded hex in components):
- xp-orange        #D94E00  main accent: buttons, highlights, links (deep dark orange)
- xp-orange-hover  #E85B07  hover state
- xp-orange-press  #BC4300  pressed state
- xp-black         #000000  page background (pure pitch black)
- xp-surface       #0C0C0E  cards and panels (deep obsidian surface)
- xp-border        #1E1E22  borders and dividers
- xp-text          #FFFFFF  main text
- xp-muted         #A1A1A6  secondary text
- xp-away          #6B6B70  gray in charts
- Status badges (inspector only): xp-ok #3DDC84, xp-error #FF4D4D, cached uses xp-muted. Never use these colours anywhere else.

Rules:
- Text on orange is black. White on orange only has about 2.9:1 contrast and fails.
- Orange text only on black or surface backgrounds.
- Body text at least 4.5:1. Visible focus ring: 2px orange with offset.
- Charts: home win in orange, draw in white, away win in gray.
- Fonts: Space Grotesk Bold for headings and the wordmark, system font for body text, monospace for JSON.
- Texture: an optional faint honeycomb pattern (3-6% opacity) on page backgrounds and hero panels only, never behind text or tables.

Logo & Favicon:
- Mark = a dark orange hexagon (#D94E00) enclosing a 4-bar analytics histogram (medium-low, peak, medium, short) and pure black baseline (#000000).
- Wordmark = lowercase "xpect" in pure white (#FFFFFF).
- Favorite Icon / Favicon: the dark orange hexagon mark (`public/brand/mark-orange.svg`, `public/favicon.ico`).
All production SVG and PNG assets are located in `public/brand/`.

Implementation: define the tokens once as Tailwind theme colours
(tailwind.config.js, namespace xp) and CSS variables. Real buttons and links,
touch targets at least 44px.

Used by: the /dev/api inspector now, the full UI in Phase 4.
