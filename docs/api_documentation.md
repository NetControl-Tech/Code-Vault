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

### POST `/subscription-activate`
Activate a subscription using a manually issued redemption code (reseller / promo / direct sale).
Validates the code, marks it **used (single-use)**, links the subscription to the device, and records
which `device_id` redeemed which code for audit. **Rate limited: 3 attempts / 15 min per IP and
device** (shares the throttle with `/device/activate`). `expiry_date` is **ISO 8601 UTC**.

```json
// Request
{
    "code": "SUBSCRIPTION-CODE",
    "device_id": "UD7VD7-DSHG76-JDBCG6"
}

// Response 200 (Success)
{
    "status": true,
    "message": "Subscription activated successfully",
    "expiry_date": "2027-06-18T00:00:00Z"
}

// Response 400/403 (Invalid or already-used code)
{ "status": false, "message": "Invalid or already used code" }

// Response 403 (Device already subscribed)
{ "status": false, "message": "This device already has an active subscription" }

// Response 429 (Rate limited)
{ "status": false, "message": "Too many attempts. Please try again later." }
```

### POST `/get-subscription-info`
Returns the current subscription state for a device. Public — the app sends `device_id` in the body
(no token). Called on launch and **polled every few seconds after a Google Play purchase** until
`is_active` becomes `true`. Reflects subscriptions from **both** manual codes and Google Play IAP,
since both are linked to the same `device_id`. An unknown device, no subscription, or an expired
subscription all return `is_active: false` (a `200`, never an error). `expiry_date` is **ISO 8601 UTC**
when active, otherwise `null`.

```json
// Request
{ "device_id": "UD7VD7-DSHG76-JDBCG6" }

// Response 200 (Active subscription)
{ "status": true, "is_active": true, "expiry_date": "2027-06-18T00:00:00Z" }

// Response 200 (No active subscription / unknown device / expired)
{ "status": true, "is_active": false, "expiry_date": null }

// Response 400 (Missing device_id)
{ "status": false, "message": "The device id field is required." }
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

### GET `/ad-block-list` 🔒 (Device Token)
Returns the **complete** list of ad/tracker domains the app blocks at the DNS level. The mobile app
fetches this on launch and refreshes it periodically; any domain in the list returns a null DNS
response so ads never load.

- **Body:** none.
- **Auth:** device token only (`Authorization: Bearer {token}`).
- **Not paginated** — the entire `ads`-category list is returned, sorted, as plain strings.

```json
// Response 200
{
    "status": true,
    "domains": [
        "ads.google.com",
        "ads.facebook.com",
        "doubleclick.net",
        "pagead2.googlesyndication.com"
    ]
}
```

### GET `/family-safety-list` 🔒 (Device Token)
Returns the **complete** list of family-safety / parental-control domains (adult content, gambling,
and other age-restricted sites). Same DNS-interception mechanism as the ad-block list. The filter is
toggled on/off by the parent in-app, but the list always comes from the server. Kept **separate**
from the ad-block list so the two filters can be toggled independently from the parent dashboard.

- **Body:** none.
- **Auth:** device token only (`Authorization: Bearer {token}`).
- **Not paginated** — the entire `family`-category list is returned, sorted, as plain strings.

```json
// Response 200
{
    "status": true,
    "domains": [
        "bet365.com",
        "pornhub.com",
        "xnxx.com",
        "xvideos.com"
    ]
}
```

### GET `/apps-list` 🔒 (Device Token)
Returns the global list of apps with their internet-access configuration (managed by the parent in
the admin panel). The device applies the config to each installed app.

- **Body:** none.
- **Auth:** device token only (`Authorization: Bearer {token}`).
- **Not paginated** — the entire list is returned, sorted by package name.

| Field | Type | Description |
|-------|------|-------------|
| `package_name` | string | Android package identifier for the app |
| `internet_block` | boolean | `true` = hard block via VPN. `false` = allow access but show a popup warning. |

```json
// Response 200
{
    "status": true,
    "apps": [
        { "package_name": "com.facebook.katana", "internet_block": true },
        { "package_name": "com.google.android.youtube", "internet_block": true },
        { "package_name": "com.puzzlegame.app", "internet_block": false }
    ]
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

## 7b. Admin: App Management 🔒 (Super Admin)

Manage the global app access list served to devices via `GET /apps-list`.

### GET `/admin/apps`
List managed apps (paginated).

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `search` | string | | Filter by package name |
| `per_page` | int | 15 | Results per page |

```json
// Response 200
{
    "current_page": 1,
    "data": [{ "id": 1, "package_name": "com.facebook.katana", "internet_block": true, "created_at": "..." }],
    "last_page": 1, "total": 3
}
```

### POST `/admin/apps`
Add an app. `internet_block` is optional and **defaults to `true`** (hard-blocked until allowed).

```json
// Request
{ "package_name": "com.facebook.katana", "internet_block": true }

// Response 201
{ "status": "success", "message": "App added successfully.", "app": { ... } }
```

### PUT `/admin/apps/{id}`
Update an app (e.g. toggle `internet_block`).

```json
// Request
{ "package_name": "com.facebook.katana", "internet_block": false }

// Response 200
{ "status": "success", "message": "App updated successfully.", "app": { ... } }
```

### DELETE `/admin/apps/{id}`
```json
// Response 200
{ "status": "success", "message": "App deleted successfully." }
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
