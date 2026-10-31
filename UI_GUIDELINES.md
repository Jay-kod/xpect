# UI_GUIDELINES.md

THEME: Xpect orange accents with user-selectable page palettes. The default is
the original pitch-black dark theme. The sidebar Themes page offers Pitch Black,
Obsidian, Midnight, and Light palettes. Built-in palettes remain fixed; create,
edit, and delete browser-local custom palettes from the Themes page. Editing a
built-in current theme previews changes live and saves them as a custom copy;
the built-in preset remains unchanged. Palette changes never affect prediction
or match data.

Colour tokens (define palette values centrally; components use these tokens):
- xp-orange        #D94E00  main accent: buttons, highlights, links (deep dark orange)
- xp-orange-hover  #E85B07  hover state
- xp-orange-press  #BC4300  pressed state
- xp-black         page background; palette-specific
- xp-surface       cards and panels; palette-specific
- xp-border        borders and dividers; palette-specific
- xp-text          main text; palette-specific
- xp-muted         secondary text; palette-specific
- xp-away          neutral gray for subdued content and charts; palette-specific
- xp-sidebar       sidebar background; palette-specific
- xp-surface-hover, xp-surface-active, xp-border-subtle, xp-border-light,
  xp-orange-hover, xp-orange-press  additional editable surface/accent states
- theme-preview-*  fixed swatch colors used only to preview each named palette
- Status badges (inspector only): xp-ok #3DDC84, xp-error #FF4D4D, cached uses xp-muted. Never use these colours anywhere else.

Rules:
- Text on orange is black. White on orange only has about 2.9:1 contrast and fails.
- Orange text only on backgrounds with sufficient contrast.
- Body text at least 4.5:1. Visible focus ring: 2px orange with offset.
- Charts: home win in orange, draw in the neutral text token (white in dark palettes), away win in gray.
- The outcome and status colours retain their semantic meaning across palettes.
- Fonts: Space Grotesk Bold for headings and the wordmark, system font for body text, monospace for JSON.
- Texture: an optional faint honeycomb pattern (3-6% opacity) on dark page backgrounds and hero panels only, never behind text or tables.

Logo & Favicon:
- Mark = a dark orange hexagon (#D94E00) enclosing a 4-bar analytics histogram (medium-low, peak, medium, short) and pure black baseline (#000000).
- Wordmark = lowercase "xpect" in the active text token; the official dark logo remains available for dark surfaces.
- Favorite Icon / Favicon: the dark orange hexagon mark (`public/brand/mark-orange.svg`, `public/favicon.ico`).
All production SVG and PNG assets are located in `public/brand/`.

Implementation: define the tokens once as Tailwind theme colours
(tailwind.config.js, namespace xp) and CSS variables. Real buttons and links,
touch targets at least 44px.

Used by: the /dev/api inspector and application UI. Appearance preferences and
browser-local custom palettes are managed from `/themes`.
