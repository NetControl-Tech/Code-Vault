# Letter Management System

A comprehensive digital solution for managing organizational correspondence, tracking workflows, and archiving documents.

## 🚀 Key Features

### 📨 Correspondence Management

- **Central Registry:** Dedicated tracking for **Incoming (الوارد)** and **Outgoing (الصادر)** letters with automated numbering.
- **Advanced Workflow:**
    - **Assignments:** Route letters to specific Departments and Employees.
    - **Deadlines:** Set reply deadlines and monitor Late Letters.
    - **Status Tracking:** Real-time progress monitoring (New, In Progress, Replied).
- **Digital Archive:**
    - **Attachments:** Multi-file support (PDF, Images) with direct preview.
    - **Linked Correspondence:** Chain related letters (Parent/Child threads).

### 📊 Dashboard & Reports

- **Interactive Statistics:** Real-time charts for letter status and category distribution.
- **Late Letters Alert:** Immediate visibility of overdue correspondence.
- **Exports:** High-quality PDF and Excel reports.

### ⚙️ Administration

- **Role-Based Access Control (RBAC):** Secure access for Admins and Employees.
- **Dynamic Settings:** Manage Categories, Subjects, and Letter Statuses.

## 🛠️ Tech Stack

### Backend

- **Framework**: [Laravel 12.x](https://laravel.com)
- **Features**: Spatie Permission (RBAC), Excel/PDF Exports, Arabic Support.

### Frontend

- **Framework**: [Vue 3](https://vuejs.org/)
- **UI & Styling**: PrimeVue 4, Tailwind CSS 4
- **State**: Pinia

## 📦 Getting Started

### Quick Setup

```bash
#  Backend Setup
composer install
php artisan migrate
php artisan key:generate
php artisan storage:link
php artisan serve

# Frontend Setup
cd frontend
npm install
npm run dev
```
