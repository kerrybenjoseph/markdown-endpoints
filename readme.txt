=== Markdown Endpoints ===
Contributors: kerrybenjoseph
Tags: markdown, ai seo, geo, llm, rest api
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Make your WordPress site readable by AI. Serve any post, page, or custom post type as clean Markdown by appending .md to any URL.

== Description ==

AI engines, LLMs, and tools like ChatGPT, Perplexity, and Claude crawl the web to build their knowledge — and they prefer clean, structured Markdown over HTML. Markdown Endpoints adds a `.md` endpoint to every public post, page, and custom post type on your WordPress site with zero configuration.

**For SEO and GEO practitioners:** Make your content machine-readable and AI-indexable. Append `.md` to any URL and serve clean Markdown that AI engines can actually parse and cite. This is a foundational step in Generative Engine Optimization (GEO) — getting your site's content surfaced in AI-generated answers.

**For WordPress developers:** A lightweight, no-frills utility that adds `.md` endpoints across your entire site automatically. Works with all public custom post types, requires no .htaccess changes, and has no admin UI or database writes.

**How it works:**

Append `.md` to any post, page, or CPT URL:

* `/about.md`
* `/blog/my-post.md`
* `/resources/case-study.md`
* `/index.md` (front page)

**Features**

* Full HTML → Markdown conversion: headings, bold, italic, links, images, lists, tables, code blocks, blockquotes
* Works with all public custom post types automatically — no configuration needed
* nginx, Apache, and LiteSpeed compatible (no .htaccess or server config required)
* `X-Robots-Tag: noindex` on `.md` responses — keeps duplicate content out of Google
* 1-hour `Cache-Control` header for CDN and reverse proxy edge caching
* No database writes, no options table pollution, no upsells
* Lightweight: three focused PHP files, no dependencies

Large language models and AI crawlers process Markdown far more cleanly than HTML. By exposing `.md` endpoints, you give AI engines a structured, noise-free version of your content — no nav, no scripts, no ads. This improves the likelihood that your content is accurately understood, cited, and surfaced in AI-generated responses.

This plugin is part of a broader practice called Generative Engine Optimization (GEO). Learn more at [kerrybenjoseph.com](https://kerrybenjoseph.com) by Kerry Ben-Joseph.

== Installation ==

1. Upload the `markdown-endpoints` folder to `/wp-content/plugins/`
2. Activate the plugin in **Plugins → Installed Plugins**
3. Test by appending `.md` to any post or page URL

Permalink rules are flushed automatically on activation — no manual Settings → Permalinks step required.

== Nginx, Apache, and LiteSpeed Note ==

This plugin uses WordPress query vars for routing — no `.htaccess` or server config changes needed. It works out of the box on nginx, Apache, and LiteSpeed hosts.

== Frequently Asked Questions ==

= Will this hurt my SEO? =

No. Every `.md` response includes an `X-Robots-Tag: noindex` header so Google and other traditional search engines won't index the Markdown versions of your pages. Your canonical HTML pages are unaffected.

= Does it work with custom post types? =

Yes. All public custom post types are supported automatically — no configuration required.

= Does it work on nginx, Apache, and LiteSpeed? =

Yes. The plugin uses WordPress's native rewrite API and query vars, so no server-level configuration is needed on nginx, Apache, or LiteSpeed.

= Does it add anything to the database? =

No. There are no options, no tables, no database writes of any kind.

= What content gets converted? =

The same content that renders on your public-facing page — post title plus body content after `the_content` filters run. Shortcodes, blocks, and builder output are all included.

= The .md output looks empty or outdated. What do I do? =

This is almost always a caching issue. Try the following in order:

1. Open the `.md` URL in an incognito or private browsing window — this bypasses browser cache and logged-in session cache
2. If you use a caching plugin (WP Rocket, W3 Total Cache, LiteSpeed Cache, etc.), purge the cache for that page
3. If you're behind a CDN (Cloudflare, Fastly, etc.), purge the CDN cache for that URL
4. If you're on a managed host with server-level caching (WP Engine, Kinsta, etc.), purge via your hosting dashboard

The plugin itself has no cache — if the output is stale, the cache layer above it is serving an old response.

== Changelog ==

= 1.1.0 =
* Improved Markdown output cleanliness — strips whitespace-only lines and collapses excess blank lines produced by page builder wrapper elements

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
Cleaner Markdown output — reduced whitespace noise from page builder wrapper elements.

= 1.0.0 =
Initial release.
