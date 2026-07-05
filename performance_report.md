# WearvaBack Performance Report

> Generated: 2026-06-17
> Project: Laravel 11 eCommerce API

---

## Executive Summary

| Area | Score | Status |
|------|-------|--------|
| N+1 Prevention | 10/10 | ✅ All controllers eager load relationships |
| Queue Usage | 9/10 | ✅ All emails queued, rate-limited |
| Caching | 0/10 | ❌ No caching implemented anywhere |
| Database Indexes | 5/10 | ⚠️ Missing critical indexes on orders table |
| Pagination | 8/10 | ✅ Most list endpoints paginated |
| Middleware | 8/10 | ✅ No heavy middleware bottlenecks |
| Laravel Optimization | 3/10 | ⚠️ No config/route/event caching |

**Overall: 6/10** — Solid architecture, but caching and indexing need immediate attention.

---

## 1. N+1 Queries ✅ (10/10)

All controllers use `with()` or `load()` for eager loading. **No N+1 problems found** in any request path.

| Controller | Loaded Relationships |
|---|---|
| `ProductController` | `primaryImage`, `images`, `variants`, `category.subCategories` |
| `OrderController` | `orderItems.product.primaryImage`, `user`, `address` |
| `CartController` | `cartItems.product.primaryImage`, `cartItems.variant` |
| `CategoryController` | `subCategories` |
| `WishlistController` | `product.primaryImage` |
| `ReviewController` | `user:id,name` |

### Concerns

| Issue | Location | Impact |
|---|---|---|
| `inRandomOrder(42)` | `ProductController::index()` | `ORDER BY RAND()` cannot use indexes; slows significantly above 10k products |
| Correlated subquery `SELECT MIN(price)` | `ProductController::index()` (sort) | Runs per row; consider computed column |
| `ReviewObserver` recalculates rating | `ReviewObserver` | 2 queries on every review change — should be queued at scale |
| `Setting::pluck('value', 'key')` | `OrderService`, `SettingController` | Hits DB every time — no caching |

---

## 2. Caching ❌ (0/10)

**Zero caching implemented.** No `Cache::remember()`, no Redis usage, no response caching.

### Current Cache Config

| Setting | Value |
|---|---|
| Driver | `database` (MySQL) |
| Redis configured | ✅ Yes, but **unused** |

### Missed Opportunities

| Candidate | Why Cache |
|---|---|
| Product listings | Filters + sorting hit DB every page load |
| Category tree | Static data, rarely changes |
| Settings | Read on every order placement |
| Dashboard stats | Stale data is acceptable |
| Review ratings | Already denormalized, but no cache around it |

### Recommendation

Switch cache driver to `redis` and use `Cache::remember()`:

```php
Cache::remember('categories.tree', 3600, fn() => Category::with('subCategories')->get());
Cache::remember('settings.all', 3600, fn() => Setting::pluck('value', 'key'));
Cache::remember("product.$id", 300, fn() => Product::with(...)->find($id));
```

---

## 3. Database Indexes ⚠️ (5/10)

### Existing Indexes (Good)

| Table | Columns | Type |
|---|---|---|
| `users` | `email` | UNIQUE |
| `products` | `slug` | UNIQUE |
| `categories` | `slug` | UNIQUE |
| `product_variants` | `(product_id, color)` | UNIQUE |
| `reviews` | `(user_id, product_id)` | UNIQUE |
| `coupons` | `code` | UNIQUE |
| `settings` | `key` | UNIQUE |

### Missing — HIGH Priority

| Table | Columns | Query Pattern |
|---|---|---|
| `orders` | `status`, `created_at` | `WHERE status = ? ORDER BY created_at DESC` |
| `orders` | `user_id`, `created_at` | `WHERE user_id = ? ORDER BY created_at DESC` |
| `wishlists` | `(user_id, product_id)` | `WHERE user_id = ? AND product_id = ?` (no unique constraint) |
| `wishlists` | `user_id` | `WHERE user_id = ?` |
| `cart` | `user_id` | `WHERE user_id = ?` |

### Recommended

```sql
CREATE INDEX orders_status_created_at_idx ON orders(status, created_at);
CREATE INDEX orders_user_id_created_at_idx ON orders(user_id, created_at);
CREATE UNIQUE INDEX wishlists_user_id_product_id_unique ON wishlists(user_id, product_id);
```

---

## 4. Queue & Mail ✅ (9/10)

### Configuration

| Setting | Value |
|---|---|
| Driver | `database` (MySQL) |
| Retry after | 90 seconds |
| Rate limit | 1 email/sec via `RateLimited('mailtrap')` |

### All Mailables Are Queued

| Mailable | Queue | Rate Limited |
|---|---|---|
| `OrderConfirmationMail` | `order_mail` | ✅ |
| `OrderStatusUpdatedMail` | `order_mail` | ✅ |
| `LoginAlertMail` | `login_alert` | ✅ |
| `LogoutAlertMail` | `logout_alert` | ✅ |

### Event Listeners Are Queued

| Event | Listener | Queued |
|---|---|---|
| `UserLoggedIn` | `SendLoginNotification` | ✅ `ShouldQueue` |
| `UserLoggedOut` | `SendLogoutNotification` | ✅ `ShouldQueue` |

### Recommendation

Switch queue driver from `database` to `redis` for better throughput. The database driver uses `SELECT ... FOR UPDATE` on every poll.

---

## 5. Pagination ✅ (8/10)

### Paginated Endpoints

| Endpoint | Page Size | Method |
|---|---|---|
| `GET /products` | 6 (max 50) | `paginate()` |
| `GET /orders` | 5 | `simplePaginate()` |
| `GET /admin/orders` | 5 | `simplePaginate()` |
| `GET /admin/coupons` | 10 | `paginate()` |
| `GET /admin/users` | 10 | `paginate()` |
| `GET products/{product}/reviews` | 10 | `paginate()` |

### Non-Paginated (Acceptable)

- `GET /categories` — inherently few records
- `GET /wishlist` — per-user, limited
- `GET /address` — per-user, limited
- `GET /cart` — per-user, limited

---

## 6. Middleware ✅ (8/10)

### Stack Per Route Group

| Group | Middleware |
|---|---|
| Auth | `throttle:auth` (3/min) |
| Webhook | `throttle:webhook` (20/min) |
| Authenticated | `auth:sanctum` |
| Customer | `auth:sanctum`, `role:customer`, `check.active` |
| Customer + Sensitive | + `throttle:sensitive` (10/min) |
| Public API | `throttle:api` (60/min) |
| Admin | `auth:sanctum`, `role:admin`, `throttle:admin` (100/min) |

### Issues Found

- `CheckPermission` middleware is **dead code** — defined but never registered
- `SetLocale` runs on every API request (minimal cost)
- Spatie `role:` middleware runs a permissions query on every request in those groups

---

## 7. Laravel Optimization ⚠️ (3/10)

### Status

| Command | Used? | Benefit |
|---|---|---|
| `config:cache` | ❌ | Merges config files into single cached file |
| `route:cache` | ❌ | Caches route registration |
| `event:cache` | ❌ | Caches event/listener mappings |
| `optimize` | ❌ | Runs all of the above |

### Recommendation

For production:

```bash
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan optimize
```

---

## 8. PHP Configuration

| Setting | Value |
|---|---|
| PHP version | 8.2+ |
| Composer autoload optimization | ✅ `optimize-autoloader: true` |
| Prefer dist | ✅ |
| OPCache | ❌ **Not configured** |

### OPCache

No `opcache.ini` found. Add for production:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
```

---

## 9. Security / Config Concerns

| Issue | Detail |
|---|---|
| Sanctum tokens never expire | `expiration => null` |
| CSRF disabled globally | `validateCsrfTokens(except: ['*'])` |
| CORS hardcoded to localhost | Only `localhost:5173/4/5` |
| `APP_DEBUG=true` | Should be false in production |
| Telescope enabled | Logs everything — disable in production |

---

## Priority Action Plan

### Critical — Fix Immediately

1. **Add indexes** to `orders.status`, `orders.created_at`, `orders.user_id`
2. **Add unique composite index** to `wishlists(user_id, product_id)`
3. **Add index** to `wishlists.user_id`

### High — Fix Before Production

4. **Implement caching** — start with `Setting::pluck()` and category tree
5. **Switch cache driver** from `database` to `redis`
6. **Switch queue driver** from `database` to `redis`
7. **Disable Telescope** or gate it behind `APP_ENV=local`
8. **Run `config:cache`, `route:cache`, `event:cache`** in deployment script

### Medium — Fix Soon

9. **Replace `inRandomOrder(42)`** with a sortable alternative
10. **Queue `ReviewObserver`** rating recalculation
11. **Add OPCache configuration**
12. **Set Sanctum token expiration** (e.g., 24h)

### Low — Nice to Have

13. Remove dead `CheckPermission` middleware
14. Fix `ProductImageController::setPrimary()` (comma vs fat arrow bug)
15. Add `Cache-Control` headers to storage routes
16. Add soft deletes to `products` table
17. Add CDN for product images in production

---

## Scoring Summary

| Category | Score | Why |
|---|---|---|
| N+1 Prevention | 10/10 | All relationships eager-loaded |
| Queue & Async | 9/10 | All mails queued, missing job for review recalculation |
| Caching | 0/10 | None implemented |
| Database Indexes | 5/10 | Critical gaps on `orders` table |
| Pagination | 8/10 | Non-paginated endpoints are inherently limited |
| Middleware | 8/10 | No bottlenecks |
| Laravel Optimization | 3/10 | No config/route/event caching |
| PHP Config | 5/10 | No OPCache config |
| **Overall** | **6/10** | Solid foundations, caching + indexing will unlock next level |
