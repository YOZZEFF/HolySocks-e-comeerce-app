# Wearva Backend — Performance & Scale Analysis

## Overview

- **Framework:** Laravel (Sanctum auth, Spatie roles/permissions)
- **Database:** MySQL (`wearva`)
- **Cache Driver:** `database` (not Redis, despite Redis being configured)
- **Queue Driver:** `database` (MySQL `jobs` table)
- **Environment:** Telescope enabled (`TELESCOPE_ENABLED=true`)

---

## 1. Database — Missing Indexes

| Table / Column | Query Pattern | Risk |
|---|---|---|
| `orders.status` | `WHERE status = ?` in admin listing, dashboard stats | **HIGH** — full table scan on every filtered order query |
| `orders.created_at` | `ORDER BY created_at DESC`, `GROUP BY MONTH(created_at), YEAR(created_at)` | **MEDIUM** — no index for ordering or date grouping |
| `orders.payment_status` | Filtering by payment status in webhook | **LOW** — used infrequently, but still unindexed |
| `orders.user_id` | FK present, no explicit index | **LOW** — may rely on InnoDB auto-indexing, but explicit is safer |

**Recommendation:** Add composite indexes on `orders(status, created_at)`, and `orders(user_id, created_at)`.

---

## 2. Caching — Not Used Anywhere

- **No `Cache::remember()`** calls anywhere in controllers, services, or repositories.
- Every request hits the database directly — no query caching, no response caching, no model caching.
- The configured cache store is `database` (MySQL `cache` table), which adds overhead per cache operation. Redis is configured but unused.

**Recommendation:** Use `Cache::remember()` for:
  - Product listings (with tags for invalidation on create/update)
  - Category tree
  - Product `getColorsAttribute()` (currently hits filesystem per product)
  - Dashboard stats (stale-while-revalidate acceptable)

---

## 3. Filesystem I/O Per Product — `getColorsAttribute()`

In `Product.php`, `getColorsAttribute()` calls `File::directories()` on `storage/app/public/products/{slug}/` every time a product is serialized. If 100 products are loaded in a listing, this triggers 100 filesystem scans.

**Recommendation:** Cache the result per product slug, or store colors as a JSON column on the `products` table.

---

## 4. Query Patterns

### Good (already optimized)
- All controllers use `with()` or `load()` for eager loading — no obvious N+1 in request paths.
- Pagination applied on: products (50), orders (5), reviews (10), users (10), coupons (5).
- DB transaction wrapping order placement.
- Emails queued (`ShouldQueue`).

### Concerning
| Pattern | Severity | Detail |
|---|---|---|
| `inRandomOrder(42)` in product listing | **LOW** | `ORDER BY RAND()` does not use indexes; slow on large datasets |
| `ReviewObserver` recalculates avg_rating on every create/update/delete | **LOW** | Two queries per event; acceptable at low volume, could queue at scale |
| `User::role('customer')->count()` in dashboard | **LOW** | Spatie role scope join — slow on large user tables |
| `Order::count()`, `Product::count()` in dashboard | **LOW** | Full table scans for dashboard numbers |

---

## 5. Queue & Async Processing

- Queue driver is `database` (MySQL), which is the **least performant** option — each job poll requires a `SELECT ... FOR UPDATE` query.
- Only 2 mailables are queued (`OrderConfirmationMail`, `OrderStatusUpdatedMail`).
- No custom jobs for heavy operations (e.g., review rating recalculation, image processing).

**Recommendation:** Move to Redis for queue driver. Add dedicated jobs for heavy operations.

---

## 6. Authentication & Rate Limiting

- Sanctum tokens have **no expiration** (`expiration => null`).
- Auth endpoints throttled at **5 requests/minute** — very conservative. Fine for security but users hitting login/register repeatedly will be blocked quickly.
- `CheckPermission` middleware is defined but **never registered in routes** — dead code.

---

## 7. Schema Concerns

| Issue | Detail |
|---|---|
| `wishlists` lacks composite unique key | Duplicate entries possible (controller checks via `exists()`, but DB-level constraint missing) |
| No soft deletes on products | `products` table lacks `deleted_at` — hard deletes only |
| Telescope enabled | `TELESCOPE_ENABLED=true` in `.env` — if `APP_ENV=production`, Telescope logs every query/request to the database, a major performance drag |

---

## 8. CORS

CORS allows `localhost:5173,5174,5175` — hardcoded origins. Not a scaling concern but worth noting for production deployment.

---

## Priority Summary

| Priority | Action |
|---|---|
| **Critical** | Add indexes on `orders.status`, `orders.created_at` |
| **High** | Implement query/response caching (product listings, categories) |
| **High** | Disable or properly gate Telescope in production |
| **Medium** | Cache or refactor `getColorsAttribute()` to avoid filesystem I/O |
| **Medium** | Switch cache + queue driver from `database` to Redis |
| **Low** | Add composite unique on `wishlists(user_id, product_id)` |
| **Low** | Remove dead `CheckPermission` middleware |
| **Low** | Set Sanctum token expiration |
