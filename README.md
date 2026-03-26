# Markdown Endpoints

**A WordPress plugin that makes your site optimized to be readable by AI.**

Append `.md` to any WordPress URL and serve clean, structured Markdown — no configuration, no admin UI, no database writes.

```
/about          →  /about.md
/blog/my-post   →  /blog/my-post.md
/               →  /index.md
```

---

## Why

LLMs and AI crawlers (ChatGPT, Perplexity, Claude) parse Markdown far more cleanly than HTML. WordPress powers 43% of the web. Almost none of it is optimized to be readable by AI.

Markdown Endpoints bridges that gap. It's a foundational step in [Generative Engine Optimization (GEO)](https://kerrybenjoseph.com) — getting your content accurately understood, cited, and surfaced in AI-generated answers.

---

## What it does

- Serves any public post, page, or custom post type as clean Markdown via a `.md` URL
- Converts HTML → Markdown: headings, bold, italic, links, images, lists, tables, code blocks, blockquotes
- Strips `<style>` and `<script>` nodes before conversion — clean output for AI consumers
- Sets `X-Robots-Tag: noindex` on `.md` responses — keeps duplicate content out of Google
- Sets `Cache-Control: public, max-age=3600` for CDN and reverse proxy edge caching
- Flushes permalink rules automatically on activation and deactivation
- Works on nginx, Apache, and LiteSpeed — no `.htaccess` or server config changes needed
- No admin UI, no options table, no database writes

---

## Installation

**From the WordPress plugin directory:**

1. Go to **Plugins → Add New** in your WordPress dashboard
2. Search for **Markdown Endpoints**
3. Click **Install Now**, then **Activate**

**Manual install:**

1. Download the latest release zip
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the zip and activate

That's it. No configuration required.

---

## How it works

The plugin registers `.md` rewrite rules via WordPress's native Rewrite API and routes requests using query vars — not server-level rewrites. This means it works on any host without touching `.htaccess` or nginx config.

On activation, rewrite rules flush automatically. On deactivation, they flush and clean up.

The HTML-to-Markdown conversion uses PHP's `DOMDocument` rather than regex, making it robust against real-world messy HTML from Gutenberg, Elementor, and other builders. `<style>` and `<script>` nodes are removed from the DOM before conversion runs.

---

## Requirements

- WordPress 5.0+
- PHP 7.4+

---

## Roadmap

| Status | Item |
|--------|------|
| ✅ Shipped | `.md` endpoints for all public post types and CPTs |
| ✅ Shipped | Style and script tag stripping for clean AI output |
| ✅ Shipped | Automatic permalink flush on activation / deactivation |
| ✅ Shipped | Whitespace normalisation — clean output from page builder sites |
| 🔲 Backlog | Additional noise reduction — inline styles, data attributes |
| 🔲 Backlog | Optional image stripping — toggle to exclude images for pure text output |

---

## Contributing

Bug reports and pull requests are welcome. For major changes, open an issue first to discuss what you'd like to change.

## Troubleshooting

**The .md output looks empty or outdated**

This is almost always a caching issue. Try opening the `.md` URL in an incognito window first — this bypasses browser cache and any logged-in session cache. If that shows the correct output, purge your caching plugin, CDN, or host-level cache for that URL.

The plugin has no cache of its own. If the output is stale, something above it is serving an old response.

---

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author

Built by [Kerry Ben-Joseph](https://kerrybenjoseph.com) — web developer, product owner, and GEO practitioner.

[markdownendpoints.com](https://markdownendpoints.com) · [WordPress.org listing](https://wordpress.org/plugins/markdown-endpoints/)
