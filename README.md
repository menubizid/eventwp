# 🎽 EventWP — Sport Event Active Nation

Plugin **full-stack WordPress** untuk CMS Sport Event *Active Nation* — jual tiket (Zumba Step, 3x3 Basket, Padel, Yoga, Run) dengan REST API, Gutenberg blocks, widget siap pakai, shortcode, dan aplikasi web mobile. **Data tersimpan di database yang sama dengan WordPress** (MySQL/MariaDB, dikelola via phpMyAdmin/cPanel).

---

## ✨ Highlights

| Area | Isi |
| --- | --- |
| 🎨 Landing Page | `[eventwp_landing]` — premium, modern, conversion-focused (sticky navbar, hero, sport event, benefits, CTA, footer) + animasi scroll-reveal, glassmorphism, ambient motion |
| 📱 App Web Mobile | `[eventwp_app]` / blok **EventWP — App** — login HP, discovery by kota, checkout, voucher, QRIS/transfer, e-ticket QR, check-in, form dinamis, ulasan, galeri |
| 🧩 Gutenberg | Post type `eventwp_event` + taksonomi `eventwp_category` + 3 blok siap pakai |
| 🔌 REST API | `eventwp/v1/*` — auth Bearer token, struktur respons `{success, message, data, timestamp}` |
| 🗄️ Database | Tabel `wp_eventwp_*` di database WordPress + export/import backup JSON |

---

## 🚀 Instalasi

1. Salin folder plugin ke `wp-content/plugins/eventwp` (File Manager cPanel / FTP).
2. Aktifkan plugin → tabel & data demo dibuat otomatis.
3. Buat halaman:
   - **Aplikasi**: blok **EventWP — App Sport Event** atau `[eventwp_app]`
   - **Landing**: `[eventwp_landing]`
   - **Daftar event**: `[eventwp_events limit="6" filter="1"]`
   - **Detail event**: `[eventwp_event id="1"]`

## 🔑 Akun Demo

| Role | ID | Password |
| --- | --- | --- |
| Customer | `6281230000003` | `customer123` |
| Instructor | `6281230000002` | `coach123` |
| Admin | `6281230000001` | `admin123` |

## 🔌 REST API (ringkas)

```bash
# Login (hasil → token)
curl -X POST {site}/wp-json/eventwp/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier":"6281230000003","password":"customer123"}'

# Event publik
curl {site}/wp-json/eventwp/v1/events

# Profil (butuh token)
curl {site}/wp-json/eventwp/v1/me -H "Authorization: Bearer {token}"
```

Endpoint utama: `auth/register`, `auth/login`, `config`, `events`, `events/{id}`, `categories`, `payments`, `vouchers/check`, `me`, `checkout`, `orders`, `tickets`, `tickets/history`, `forms/submit`, `reviews`, `gallery`, `instructor/schedule`, `instructor/feedback`, `admin/*` (overview, events, categories, templates, payments, instructors, vouchers, orders, orders/validate, checkin, reports, responses, settings, settings/send, audit, backup/export).

## 🗄️ Skema Database

`users`, `events`, `event_categories`, `tickets`, `orders`, `form_templates`, `form_responses`, `payments`, `vouchers`, `reviews`, `galleries`, `settings`, `audit_logs` — semuanya di database WordPress (prefix `wp_` default).

> Backup: **Active Nation → Pengaturan → Download Backup JSON**, atau endpoint `admin/backup/export`, atau admin app → *Export Backup JSON*.

## 🧱 Arsitektur

```
eventwp/
├── eventwp.php                      # bootstrap
├── includes/                        # bahasa PHP (EventWP_*)
│   ├── class-eventwp.php            # orchestator
│   ├── class-eventwp-installer.php  # dbDelta + seed + export
│   ├── class-eventwp-store.php      # Data Access Layer
│   ├── class-eventwp-api.php        # business layer
│   ├── class-eventwp-rest.php       # REST controller
│   ├── class-eventwp-cpt.php        # custom post type
│   ├── class-eventwp-taxonomy.php   # kategori + form template link
│   ├── class-eventwp-meta.php       # custom fields + REST meta
│   ├── class-eventwp-blocks.php     # blok gutenberg (server render)
│   ├── class-eventwp-shortcodes.php # [eventwp_*]
│   ├── class-eventwp-admin.php      # menu wp-admin + backup
│   └── class-eventwp-assets.php     # register/enqueue
├── assets/css/*.css                 # landing, app, frontend, admin
├── assets/js/*.js                   # app.js, vendor.js (QR), landing.js
├── assets/img/*                     # qris + og placeholder
├── build/                           # blok editor script
├── templates/landing.php            # template landing page
└── readme.txt
```

## 🎯 Struktur Halaman Landing

Sticky navbar → Hero (badge, headline gradien, CTA, trust stats, phone mockup + kartu melayang) → Marquee → Sport Events → Upcoming Events → Benefits → Cara Kerja → Testimoni → FAQ → CTA panel → Footer.

## 🛡️ Keamanan

- Semua input di-sanitasi sebelum menyentuh DB (`sanitize_*`, whitelist kolom).
- Semua query memakai `$wpdb->prepare`.
- `LockService` (transient lock) pada aksi kritis checkout & check-in.
- Role-Based Access Control untuk customer / instructor / admin.
- Audit log mencatat aksi + IP.

## 📝 Lisensi

GPL-2.0-or-later. Dibangun untuk komunitas sport Indonesia dengan ⚡.
