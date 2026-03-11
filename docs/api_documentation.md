# CodeVault — API Documentation

> **Base URL:** `http://localhost:8000/api`
> **Auth:** All protected routes require `Authorization: Bearer {token}` header.

---

## 1. Authentication

### POST `/login`
Login with email and password. Returns a Sanctum token or triggers 2FA.

```json
// Request
{
    "email": "admin@example.com",
    "password": "password123"
}

// Response 200
{
    "status": "success",
    "token": "1|abc123...",
    "user": { "id": 1, "name": "Admin", "email": "admin@example.com" }
}
```

### POST `/verify-2fa` 🔒
Verify 2FA code after login.

```json
// Request
{ "code": "123456" }

// Response 200
{ "status": "success", "token": "1|abc123..." }
```

### POST `/resend-2fa` 🔒
Resend 2FA code. **Rate limited: 1 request/minute.**

### POST `/cancel-2fa` 🔒
Cancel 2FA and log out.

### POST `/logout` 🔒
Revoke current token.

### GET `/me` 🔒
Get authenticated user info.

---

## 2. Device Auth (V1) — NetControl App

### POST `/v1/device/activate`
Activate a device using a license PIN code. **Rate limited: 3 attempts / 15 min per IP and device.**

```json
// Request
{
    "pin_code": "ABC123DEF456",
    "device_id": "DEVICE_HARDWARE_ID_123"
}

// Response 200
{
    "status": "success",
    "token": "2|xyz789...",
    "expires_at": "2026-04-10T00:00:00.000000Z",
    "device_id": "DEVICE_HARDWARE_ID_123",
    "code": 200
}

// Response 400 (Invalid PIN)
{ "status": "error", "message": "Invalid or inactive PIN", "code": 400 }

// Response 429 (Rate limited)
{ "status": "error", "message": "Too many attempts. Please try again later." }
```

### GET `/v1/device/status` 🔒 (Device Token)
Check current subscription status.

```json
// Response 200
{
    "status": "success",
    "subscription_status": "active",
    "expires_at": "2026-04-10T00:00:00.000000Z",
    "code": 200
}
```

### POST `/v1/device/unlink` 🔒 (Device Token)
Unlink device from its license code and revoke all tokens.

```json
// Response 200
{ "status": "success", "message": "Device unlinked successfully.", "code": 200 }
```

---

## 3. Blocklists (V1) — NetControl App 🔒 (Device Token)

### GET `/v1/blocklists/family`
### GET `/v1/blocklists/social`
### GET `/v1/blocklists/ads`
### GET `/v1/blocklists/privacy`

Returns paginated domains for the specified category.

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `per_page` | int | 15 | Results per page |
| `page` | int | 1 | Page number |

```json
// Response 200
{
    "current_page": 1,
    "data": [
        { "id": 1, "domain": "baddomain.com", "category": "family", "created_at": "..." }
    ],
    "last_page": 5,
    "total": 72
}
```

---

## 4. Tools (V1) — NetControl App 🔒 (Device Token)

### POST `/v1/tools/check-url`
Check if a URL is on any blocklist.

```json
// Request
{ "url": "https://www.baddomain.com/page" }

// Response 200 (Blocked)
{
    "status": "blocked",
    "category": "family",
    "category_label": "أمان الأسرة",
    "domain_matched": "baddomain.com",
    "code": 200
}

// Response 200 (Clean)
{ "status": "clean", "message": "URL is not on any blocklist.", "code": 200 }
```

### POST `/v1/tools/report-url`
Report a URL for admin review.

```json
// Request
{ "url": "https://suspicious-site.com/path" }

// Response 201
{
    "status": "success",
    "message": "URL reported successfully. It will be reviewed soon.",
    "report": { "id": 1, "url": "...", "domain": "suspicious-site.com", "status": "pending" },
    "code": 201
}
```

---

## 5. Admin: License Codes 🔒 (Super Admin)

### GET `/admin/codes`
List license codes with filters.

| Param | Type | Description |
|-------|------|-------------|
| `search` | string | Filter by serial number |
| `status` | string | `active`, `inactive`, `redeemed` |
| `per_page` | int | Results per page (default 15) |

```json
// Response 200
{
    "current_page": 1,
    "data": [
        {
            "id": 1, "serial": 10000, "status": "active",
            "duration_days": 30, "expires_at": null, "device_hardware_id": null
        }
    ],
    "last_page": 10, "total": 150
}
```

### POST `/admin/codes/generate`
Generate a batch of license codes and download as Excel.

```json
// Request
{ "count": 100, "duration_days": 30 }

// Response: Excel file download (.xlsx)
```

### POST `/admin/codes/activate-range`
Activate inactive codes in a serial range.

```json
// Request
{ "from_serial": 10000, "to_serial": 10099 }

// Response 200
{ "status": "success", "message": "تم تفعيل 100 كود بنجاح", "activated_count": 100 }
```

### POST `/admin/codes/destroy-range`
Delete codes in a serial range.

```json
// Request
{ "from_serial": 10000, "to_serial": 10099 }

// Response 200
{ "status": "success", "message": "تم حذف 100 كود بنجاح", "deleted_count": 100 }
```

### POST `/admin/codes/{code}/renew`
Renew/extend a license code's duration.

```json
// Request
{ "duration_days": 30 }

// Response 200
{ "status": "success", "license_code": { "id": 1, "expires_at": "...", "duration_days": 60 } }
```

### GET `/admin/codes/export`
Export all codes as Excel file.

```
// Response: Excel file download (.xlsx)
```

---

## 6. Admin: Devices 🔒 (Super Admin)

### POST `/admin/devices/{device}/revoke-token`
Revoke all Sanctum tokens for a device.

```json
// Response 200
{ "status": "success", "message": "Device tokens revoked successfully." }
```

---

## 7. Admin: Blocklist Management 🔒 (Super Admin)

### GET `/admin/blocklists`
List blocklist domains for a given category.

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `category` | string | ✅ | `family`, `social`, `ads`, `privacy` |
| `search` | string | | Filter by domain |
| `per_page` | int | | Default 15 |

```json
// Response 200
{
    "current_page": 1,
    "data": [{ "id": 1, "domain": "baddomain.com", "category": "family", "created_at": "..." }],
    "last_page": 3, "total": 42
}
```

### POST `/admin/blocklists`
Add a single domain.

```json
// Request
{ "domain": "newbad.com", "category": "ads" }

// Response 201
{ "status": "success", "message": "Domain added successfully.", "domain": { ... } }
```

### PUT `/admin/blocklists/{id}`
Update a domain entry.

```json
// Request
{ "domain": "updated.com", "category": "privacy" }

// Response 200
{ "status": "success", "message": "Domain updated successfully.", "domain": { ... } }
```

### DELETE `/admin/blocklists/{id}`
```json
// Response 200
{ "status": "success", "message": "Domain deleted successfully." }
```

### POST `/admin/blocklists/bulk-upload`
Bulk import domains from a `.txt` or `.csv` file.

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `file` | file | ✅ | `.txt` or `.csv` file |
| `category` | string | ✅ | Target category |

```
Content-Type: multipart/form-data
```

```json
// Response 201
{
    "status": "success",
    "message": "Successfully inserted 150 domains.",
    "inserted_count": 150,
    "duplicate_count": 3,
    "invalid_lines_count": 2,
    "total_processed": 153
}
```

---

## 8. Admin: Reported URLs 🔒 (Super Admin)

### GET `/admin/reports`
List reported URLs.

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `status` | string | `pending` | `pending`, `approved`, `rejected` |
| `per_page` | int | 15 | |

```json
// Response 200
{
    "current_page": 1,
    "data": [{
        "id": 1, "url": "https://...", "domain": "bad.com",
        "status": "pending", "device": { "device_id": "HWID_123" },
        "created_at": "..."
    }],
    "last_page": 1, "total": 5
}
```

### POST `/admin/reports/{report}/approve`
Approve a report and add the domain to a blocklist category.

```json
// Request
{ "category": "family" }

// Response 200
{ "status": "success", "message": "Report approved and domain added to blocklist." }
```

### POST `/admin/reports/{report}/reject`
```json
// Response 200
{ "status": "success", "message": "Report rejected." }
```

---

## 9. User Management 🔒

### GET `/users`
### POST `/users`
### GET `/users/{user}`
### PUT `/users/{user}`
### POST `/users/{user}/toggle-active`
### DELETE `/users/{user}`

Standard CRUD for user management.

---

## 10. RBAC 🔒 (Super Admin)

### GET/POST `/roles`
### PUT `/roles/{role}`
### DELETE `/roles/{id}`
### POST `/roles/{role}/permissions`
### GET `/permissions`

Role-based access control management.

---

## 11. Devices (Legacy)

### GET `/devices`
List all devices (paginated, filterable by `device_id` and `is_active`).

### POST `/devices`
```json
{ "device_id": "HARDWARE_ID_123" }
```

### DELETE `/devices/{id}`
Delete a device record.
