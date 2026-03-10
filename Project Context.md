# Project Context: CodeVault

## Overview
**CodeVault** is a comprehensive digital solution for generating, distributing, and managing software license codes. Its primary purpose is to bind software licenses to precise hardware identifiers, ensuring secure, single-device software licensing.

## Tech Stack
The project follows a decoupled architecture with a robust backend API and a modern single-page application (SPA) frontend.

### Backend (API)
- **Framework**: [Laravel 12.x](https://laravel.com) running on PHP 8.2+.
- **Authentication**: Laravel Sanctum (Token-based API authentication for devices and web clients).
- **Authorization**: Spatie Laravel Permission (Role-Based Access Control - RBAC).
- **Tooling**:
  - Maatwebsite Excel (for exporting license codes to spreadsheets).
  - Barryvdh Laravel DOMPDF (for PDF generation).
- **Database**: Relational Database (SQLite/MySQL ready) using Eloquent ORM.

### Frontend (User Interface)
- **Framework**: [Vue 3](https://vuejs.org/) using the Composition API (`<script setup>`).
- **Build Tool**: Vite.
- **Styling**: 
  - Tailwind CSS v4.
  - PrimeVue 4 (UI Component Library) + PrimeIcons.
- **State Management**: Pinia.
- **Routing**: Vue Router 4.
- **Charting**: Chart.js with Vue-Chartjs.
- **HTTP Client**: Axios.

## Core Features
1. **License & Hardware Management**:
   - Hardware-locking: License codes are permanently bound to a specific device's hardware ID.
   - Licensing logic: Automatic expiration calculation upon activation, renewal capabilities, and status tracking (active, inactive, redeemed).
2. **Access & Security**:
   - RBAC for users (Super Admin, Supervisor, Client).
   - Sanctum tokens tied directly to devices, with immediate revocation capabilities.
   - Two-Factor Authentication (OTP) for admin logins.
   - Brute-force protection via rate limiting on critical endpoints (login, redemption).
3. **Admin Dashboard**:
   - Batch generation of random license codes with specific durations.
   - Range control for activating or deleting sequential serial numbers.
   - Real-time Excel exports of generated or unused codes.
   - Device and user management.

## Project Structure
- **/app**: Core Laravel backend logic (Models, Controllers, Services, Commands).
  - *Example*: `app/Console/Commands/` (Contains scheduled tasks like deactivating expired licenses).
- **/frontend**: Independent Vue 3 client application containing its own dependencies (package.json).
  - `/frontend/src/components`: Reusable UI elements (e.g., Sidebar, TopBar).
  - `/frontend/src/views` or `pages`: Page-level components.
  - `/frontend/src/services`: API interaction layer (Axios configurations).
- **/routes**: Backend routing (API & Console).
- **/database**: Migrations, seeders, and factories for testing and setup.
- **/tests**: PHPUnit feature and unit tests.

## Key Workflows
- **Redemption Flow**: A device sends its hardware ID and a license pin. The backend verifies the pin, binds the license to the hardware ID, issues a device-specific Sanctum token, and tracks the expiration date.
- **Admin Flow**: Admins log in (with 2FA), generate codes in bulk, export them, and monitor active devices.
