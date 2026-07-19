<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Cara Menjalankan Project

### 🛠️ 1. Mode Development (Lokal)
Jalankan 4 perintah berikut di 4 terminal yang berbeda:
```bash
php artisan serve
php artisan schedule:work
php artisan reverb:start --port=8800
php artisan queue:work --tries=3 --timeout=120
```

---

### 🚀 2. Mode Production (di aaPanel VPS)

Di lingkungan **Production (aaPanel)**, penanganan perintah sangat berbeda dengan di komputer lokal/development:
- `php artisan serve` **TIDAK DIPAKAI** karena Nginx aaPanel sudah menangani routing ke folder `/public`.
- `php artisan schedule:work` **TIDAK DIPAKAI DI SUPERVISOR** karena standar resmi Laravel di production menggunakan **Cron Job aaPanel (`schedule:run`)** setiap 1 menit agar tidak terjadi *memory leak*.
- `reverb:start` dan `queue:work` **WAJIB DIPASANG DI SUPERVISOR MANAGER aaPanel**.

#### A. Konfigurasi di Supervisor Manager (Menu App Store > Supervisor)
Pastikan **Run Directory** mengarah ke folder website kamu (misal: `/www/wwwroot/autopost.semesta.com`).

**1. Daemon Reverb (WebSocket Server):**
- **Name:** `wp-auto-post-reverb`
- **Run User:** `www`
- **Start Command:**
  ```bash
  /www/server/php/82/bin/php artisan reverb:start --host="127.0.0.1" --port=8080
  ```

**2. Daemon Queue Worker (Proses Antrean Auto Post):**
- **Name:** `wp-auto-post-queue`
- **Run User:** `www`
- **Start Command:**
  ```bash
  /www/server/php/82/bin/php artisan queue:work database --tries=3 --timeout=120 --sleep=3
  ```

#### B. Konfigurasi di Cron aaPanel (Menu Cron > Add Cron)
Untuk menjalankan penjadwalan/jadwal otomatis (Schedule), tambahkan Cron Job di aaPanel:
- **Type of Task:** `Shell Script`
- **Name:** `Laravel Scheduler AutoPost`
- **Execution cycle:** `N minutes` -> `1 Minute` *(Setiap 1 menit)*
- **Script content:**
  ```bash
  /www/server/php/82/bin/php /www/wwwroot/autopost.semesta.com/artisan schedule:run >> /dev/null 2>&1
  ```
*(Catatan: Sesuaikan `/php/82/` dengan versi PHP yang kamu pakai di aaPanel, misal `/php/83/` jika memakai PHP 8.3)*.

