=== EventWP — Sport Event Active Nation ===
Contributors: activenation
Tags: sport, event, ticket, gutenberg, woocommerce-free, booking, qr
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Full-stack CMS Sport Event (Zumba Step, 3x3 Basket, Padel, Yoga, Run) untuk WordPress — custom post type, blok Gutenberg, widget, shortcode, REST API, dan aplikasi web mobile. Data tersimpan di database yang sama dengan WordPress (phpMyAdmin / MySQL / MariaDB).

== Description ==

EventWP — Sport Event Active Nation adalah plugin lengkap untuk menjual tiket event olahraga dan mengelola seluruh siklus event: peserta (customer), instruktur (coach), hingga administrator.

= Fitur Utama =

* **Aplikasi Web Mobile** — login dengan nomor HP, discovery event berdasarkan kota domisili, checkout (maks 5 tiket), voucher diskon, pembayaran Transfer Bank & QRIS, upload bukti, e-ticket dengan QR code, check-in QR, form event dinamis, ulasan bintang, galeri event (UGC).
* **Panel Instruktur** — dashboard metrik, jadwal kelas dengan progress kapasitas, agregasi feedback peserta.
* **Panel Administrator** — CRUD event (auto slug), kategori, form builder, metode pembayaran, validasi pesanan, check-in (scan/manual), laporan kehadiran & form, WhatsApp automations, manajemen instruktur & voucher, audit logs, backup/export JSON.
* **Custom Post Types Gutenberg** — post type `eventwp_event`, taksonomi `eventwp_category`, custom fields lengkap (jadwal, harga, kapasitas, visibilitas, instruktur, galeri), siap dirowse pada menu WordPress.
* **Blok Gutenberg siap pakai** — *EventWP — Daftar Event*, *EventWP — Detail Event*, *EventWP — App Sport Event*.
* **Shortcode** — `[eventwp_app]`, `[eventwp_events limit="6"]`, `[eventwp_event id="1"]`, `[eventwp_landing]`.
* **Landing Page Premium** — `[eventwp_landing]` menampilkan halaman penjualan tiket modern (sticky navbar, hero, sport event, benefits, CTA, footer) dengan animasi scroll-reveal, glassmorphism, dan motion halus.
* **REST API** — namespace `eventwp/v1` dengan autentikasi token Bearer.
* **Database** — tabel terpisah di database WordPress (menggunakan `$wpdb` & charset WordPress), mudah dikelola via phpMyAdmin. Dukungan export/import backup JSON.

= Instalasi Cepat =

1. Upload folder `eventwp` ke `wp-content/plugins/` (melalui File Manager cPanel atau FTP).
2. Aktifkan plugin melalui menu **Plugins**.
3. Tabel database dan data contoh dibuat otomatis saat aktivasi.
4. Buat halaman baru, tambahkan blok **EventWP — App Sport Event**, atau tempel shortcode `[eventwp_app]`.
5. Untuk landing page, buat halaman dan tempel `[eventwp_landing]`.

= Akun Demo =

* Customer — ID `6281230000003` / password `customer123`
* Instructor — ID `6281230000002` / password `coach123`
* Admin — ID `6281230000001` / password `admin123`

= REST API =

Contoh login:

`curl -X POST {site}/wp-json/eventwp/v1/auth/login -H "Content-Type: application/json" -d '{"identifier":"6281230000003","password":"customer123"}'`

Gunakan token yang dikembalikan sebagai header `Authorization: Bearer {token}` pada endpoint lainnya.

= Changelog =

= 1.0.0 =
* Rilis perdana.
