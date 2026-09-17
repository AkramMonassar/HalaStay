# 🏨 HalaStay — Hotel, Apartment & Hall Booking Platform

**[English](README.md)** | **[العربية](README.ar.md)**

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?logo=vuedotjs)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Tests](https://img.shields.io/badge/Tests-20%20passing-brightgreen)
![Release](https://img.shields.io/badge/Release-v1.0.0-blue)
![License](https://img.shields.io/badge/License-MIT-lightgrey)

A modern, secure hotel management and online reservation platform built for the Saudi & Yemeni markets — solving double-booking conflicts and unifying manual payment review in one flow.

---

## 🌟 Implemented Features (v1.0.0)

### For Guests
- 🔍 City/date/guests search with live filters (stay type, stars, review score)
- 📅 Real-time availability engine: capacity rules + conflict-free unit locking
- 🧾 My Bookings dashboard with cancellation and payment receipt upload
- 🔔 In-app notifications for every status change

### For Hotel Owners
- 🏨 Full hotel lifecycle: create → admin approval → live in search
- 🛠️ Rooms & accommodation types management with inline edit / activate / suspend
- 🧾 Bookings queue (confirm / reject with reason)
- 💳 Manual payment review queue (approve / reject with receipt preview)

### For Admins
- ✅ Hotel approval dashboard with live platform stats
- 👥 Users management: search, role filter, suspend / activate
- 🌍 Cities & payment methods management
- 📊 Cross-platform bookings & payments oversight

### Platform-Wide
- 🔐 Sanctum token auth with three roles & policy-based authorization
- ⚡ Rate limiting, reference-data caching with invalidation, security headers
- 🧪 20 automated PHPUnit feature tests (auth, availability, payments, admin)
- 🐳 One-command Docker deployment (MySQL + API + Nginx frontend)

---

## 🗺️ Roadmap
- 🎨 Design system season: Arabic typography, dark mode, charts dashboards, AR/EN i18n, PWA
- 💳 Payment gateways: 🇸 Mada & Apple Pay · 🇾 Meeza & Y-Cash · 🌍 Stripe & PayPal
- ⏳ Scheduled expiry job for unpaid bookings
- 🔔 WebSocket real-time notifications

---

## 🛠️ Tech Stack
- **Backend:** Laravel 11, Sanctum, MySQL 8, PHPUnit
- **Frontend:** Vue 3 (Vite), Pinia, Vue Router, Bootstrap 5 RTL, Axios
- **Ops:** Docker Compose, Git/GitHub

---

## 📂 Project Structure
```text
halastay/
├── backend/            # Laravel 11 REST API (routes, services, policies, tests)
│   └── Dockerfile
├── frontend/           # Vue 3 SPA (views, components, stores, services)
│   └── Dockerfile
├── docker-compose.yml  # mysql + backend + frontend stack
├── README.md           # You are here
└── README.ar.md        # النسخة العربية