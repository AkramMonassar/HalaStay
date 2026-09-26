# 🏨 HalaStay — Hotel, Apartment & Hall Booking Platform

[English](README.md) | [العربية](README.ar.md)

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?logo=vuedotjs)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Tests](https://img.shields.io/badge/Tests-20%20passing-brightgreen)
![Release](https://img.shields.io/badge/Release-v2.0.0-blue)
![i18n](https://img.shields.io/badge/i18n-AR%2FEN%20%7C%20RTL%2FLTR-006c35)
![PWA](https://img.shields.io/badge/PWA-installable-6f42c1)
![License](https://img.shields.io/badge/License-MIT-lightgrey)

A modern, secure hotel management and online reservation platform built for the Saudi & Yemeni markets — solving double-booking conflicts and unifying manual payment review in one flow.

## 🌟 Implemented Features (v2.0.0)

### For Guests
- 🔍 City/date/guests search with live filters (stay type, stars, review score)
- 📅 Real-time availability engine: capacity rules + conflict-free unit locking
- 🧾 My Bookings & personal dashboard (stats + upcoming stay)
- 💳 Payment flow with receipt upload and full status history
- 🔔 In-app notifications for every status change

### For Hotel Owners
- 🏨 Full hotel lifecycle: create → admin approval → live in search
- 🛠️ Accommodation types management with image uploads
- 🧾 Bookings queue (confirm / reject with reason)
- 💳 Manual payment review queue (approve / reject with receipt preview)
- 📊 Owner analytics dashboard (bookings by status + monthly revenue)

### For Admins
- ✅ Hotel approval with live platform stats
- 👥 Users management: search, role filter, suspend / activate
- 🌍 Cities & payment methods management
- 📊 Cross-platform bookings & payments oversight with charts

### Platform-Wide
- 🔐 Sanctum token auth with three roles & policy-based authorization
- 🎨 Arabic-first design system: SCSS tokens, Tajawal typography, persistent dark mode
- 🧩 Component library: modals, toasts, skeletons & an Arabic calendar picker
- 🌐 Full AR/EN i18n with RTL/LTR switching (vue-i18n), persisted per visitor
- 📱 PWA: installable manifest + service-worker caching; performance passes (lazy images, gzip, asset caching)
- ♿ Accessibility: focus-visible outlines, reduced-motion support, honest affordances
- ⚡ Rate limiting, reference-data caching with invalidation, security headers
- 🧪 20 automated PHPUnit feature tests (auth, availability, payments, admin)
- 🐳 One-command Docker deployment (MySQL + API + Nginx frontend)

## 📸 Screenshots

### Guest Journey
| Home (AR · light · RTL) | Home (EN · dark · LTR) |
|---|---|
| ![Home AR](docs/screenshots/home-ar.png) | ![Home EN dark](docs/screenshots/home-en-dark.png) |

| Search & live filters | Hotel page: gallery + sticky booking widget |
|---|---|
| ![Search](docs/screenshots/search.png) | ![Hotel](docs/screenshots/hotel.png) |

| Hotel page: Arabic calendar picker | Booking details & payment panel |
|---|---|
| ![Hotel calendar](docs/screenshots/hotel2.png) | ![Booking details](docs/screenshots/booking.png) |

| Tourist dashboard (stats + upcoming stay) | My profile (phone & name) |
|---|---|
| ![Tourist dashboard](docs/screenshots/dashboardUser.png) | ![Profile](docs/screenshots/profileUser.png) |

### Owner & Admin Operations — side by side
| Owner analytics dashboard | Admin analytics dashboard |
|---|---|
| ![Owner dashboard](docs/screenshots/dashboardOwner.png) | ![Admin dashboard](docs/screenshots/dashboardAdmin.png) |

| My hotels list | Users management |
|---|---|
| ![My hotels](docs/screenshots/hotelOner.png) | ![Users](docs/screenshots/AdminUsers.png) |

| Hotel management (data, images, types) | Platform settings (cities & payment methods) |
|---|---|
| ![Hotel management](docs/screenshots/hotelOwnerAdminstration.png) | ![Settings](docs/screenshots/settingsAdmin.png) |

| Owner bookings queue (confirm / reject) | All bookings table (search & status filter) |
|---|---|
| ![Owner bookings](docs/screenshots/bookingOwner.png) | ![All bookings](docs/screenshots/allBookingAdmin.png) |

| Owner payment review queue | All payments oversight |
|---|---|
| ![Owner payments](docs/screenshots/paymentOwner.png) | ![Admin payments](docs/screenshots/paymentAdmin.png) |

## 🗺️ Roadmap
- ⭐ Reviews & ratings system (guest-written, aggregated into review_score)
- 💳 Payment gateways: 🇸 Mada & Apple Pay · 🇾 Meeza & Y-Cash · 🌍 Stripe & PayPal
- ⏳ Scheduled expiry job for unpaid bookings
- 🔔 WebSocket real-time notifications
- 🌍 Bilingual content columns (name_ar / name_en) for cities & hotels

## 🛠️ Tech Stack
- **Backend:** Laravel 11, Sanctum, MySQL 8, PHPUnit
- **Frontend:** Vue 3 (Vite), Pinia, Vue Router, vue-i18n, ApexCharts, Bootstrap 5 (RTL/LTR), SCSS tokens, vite-plugin-pwa
- **Ops:** Docker Compose, Git/GitHub


## 📂 Project Structure
halastay/
├── backend/ # Laravel 11 REST API (routes, services, policies, tests)
│ └── Dockerfile
├── frontend/ # Vue 3 SPA (views, components, stores, i18n, services)
│ └── Dockerfile
├── docs/screenshots/ # README gallery
├── docker-compose.yml # mysql + backend + frontend stack
├── README.md # You are here
└── README.ar.md # النسخة العربية


## 💻 Local Development
1. Start MySQL (XAMPP), then inside `backend/`: `composer install`
2. Copy `.env.example` → `.env`, then `php artisan key:generate`
3. `php artisan migrate && php artisan db:seed --class=DatabaseSeeder && php artisan db:seed --class=DemoHotelsSeeder`
4. `php artisan storage:link` then `php artisan serve`
5. Inside `frontend/`: `npm install` then `npm run dev`

## 🐳 Docker Deployment
From the root: `docker compose up -d --build`, then:
`docker compose exec backend php artisan migrate --force`
`docker compose exec backend php artisan db:seed --force`

| Service | Port |
| --- | --- |
| API | http://localhost:8080 |
| Frontend | http://localhost:8081 |
| MySQL | 3307 |

## 🧪 Tests
```bash
cd backend && php artisan test