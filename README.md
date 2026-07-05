# WEARVA — Luxury Fashion E-Commerce

> A full-featured luxury fashion e-commerce platform built with React 19, Laravel 11, and Tailwind CSS v4.

## Tech Stack

**Frontend** — `wearvafront`
- **React 19** with Vite 8
- **Tailwind CSS v4** for utility-first styling
- **TanStack React Query v5** for server state & caching
- **Zustand v5** for client-side state (auth, cart, wishlist)
- **React Router v7** for client-side routing
- **Axios** with auth interceptor for API communication
- **motion** (Framer Motion) for page/UI animations
- **Lucide React** for icons
- **Inter** font family

**Backend** — `wearvaback` (Laravel 11)
- **Laravel Sanctum** for API token authentication
- **Spatie Laravel Permissions** for role-based access (customer / admin)
- **MySQL** database with full relational schema
- RESTful JSON API

## Features

### Customer-facing
- Product catalog with category filters, price/size/color filtering, and search
- Product detail page with image gallery, variant selection (size/color), stock tracking
- Shopping cart with quantity controls and guest-to-user sync
- Full checkout flow with address management, coupon validation, and payment initiation
- Order history and order details
- User profile with avatar upload, password change, and address CRUD
- Wishlist with add/remove/toggle
- Recently viewed products (persisted to localStorage)
- Related products ("You May Also Like") based on category
- Newsletter subscription
- Responsive design (320px → 1440px+)

### Admin Dashboard
- Overview stats (revenue, orders, users)
- Product management (CRUD with image uploads and variant management)
- Order management with status updates
- User management with block/unblock
- Coupon management
- Review moderation (approve/reject)
- Category management
- Settings

### Authentication & Authorization
- User registration and login
- Role-based access: `customer` and `admin`
- Token-based authentication via Laravel Sanctum
- Protected routes with automatic session expiry handling

## Project Structure

```
wearvafront/
├── public/              # Static assets (favicon, icons)
├── src/
│   ├── api/             # API query functions
│   ├── components/      # Reusable UI components
│   │   ├── admin/       # Admin layout & shared components
│   │   ├── auth/        # Auth initialization
│   │   ├── layout/      # Header, Footer, MegaMenu, SearchBar
│   │   ├── shop/        # ProductCard
│   │   └── ui/          # Generic UI primitives
│   ├── hooks/           # Custom React hooks
│   ├── lib/             # Axios instance with interceptors
│   ├── pages/           # Route-level page components
│   │   ├── auth/        # Login, Register
│   │   ├── dashboard/   # Admin pages
│   │   ├── shop/        # Home, Shop, ProductDetail, Cart
│   │   └── user/        # Profile, Checkout, Orders
│   ├── routes/          # App router, guards
│   ├── store/           # Zustand stores (auth, cart, wishlist)
│   ├── styles/          # Global CSS with Tailwind
│   └── utils/           # Utility functions
├── index.html
├── vite.config.js
└── package.json
```

## Getting Started

### Prerequisites
- **Node.js** ≥ 18
- **PHP** ≥ 8.2
- **Composer**
- **MySQL** (or SQLite for local development)

### Frontend Setup

```bash
cd wearvafront
npm install
cp .env.example .env    # VITE_API_URL defaults to /api
npm run dev             # starts at http://localhost:5173
```

The Vite dev server proxies `/api` and `/storage` requests to the Laravel backend.

### Backend Setup

```bash
cd wearvaback
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve       # starts at http://localhost:8000
```

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `VITE_API_URL` | API base path (proxied by Vite) | `/api` |

## API Overview

All API routes are prefixed with `/api`.

### Public Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | User registration |
| POST | `/login` | User login |
| GET | `/categories` | List all categories |
| GET | `/categories/{slug}` | Category detail |
| GET | `/products` | Paginated products with filtering |
| GET | `/products/{slug}` | Product detail |
| POST | `/newsletter` | Subscribe to newsletter |

### Protected Routes (auth:sanctum)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/logout` | Logout |
| GET | `/profile` | Get user profile |
| PUT | `/profile` | Update profile |
| POST | `/profile/password` | Change password |

### Customer Routes (role: customer)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/cart` | Cart CRUD |
| GET/POST/DELETE | `/wishlist` | Wishlist CRUD |
| GET/POST/PUT/DELETE | `/address` | Address CRUD |
| GET/POST | `/orders` | Order management |
| POST | `/coupons/validate` | Validate coupon |
| POST | `/payment/initiate` | Initiate payment |

### Admin Routes (role: admin)

| Method | Endpoint | Description |
|--------|----------|-------------|
| CRUD | `/admin/categories` | Category management |
| CRUD | `/admin/products` | Product management |
| GET/PUT | `/admin/orders` | Order management |
| CRUD | `/admin/coupons` | Coupon management |
| PATCH | `/admin/reviews/{id}/approve\|reject` | Review moderation |
| GET | `/admin/users` | User management |
| GET | `/admin/stats` | Dashboard statistics |

## Scripts

```bash
npm run dev      # Start development server
npm run build    # Production build
npm run lint     # Run ESLint
npm run preview  # Preview production build
```

## Design

The UI follows a minimalist luxury aesthetic:
- **Color palette**: Black, white, and neutral grays
- **Typography**: Inter — clean, modern sans-serif
- **Layout**: Full-width hero images, generous whitespace, responsive grid
- **Interactions**: Hover reveals, image zoom, micro-animations with motion
- **Design reference**: [Luxury Fashion E-commerce Website](https://www.figma.com/design/7KDHACQ0f29LxQ2pqjBrh8/Luxury-Fashion-E-commerce-Website) (Figma)

## License

MIT
