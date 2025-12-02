# API Permissions Documentation

## Admin Account
- **Email**: `admin@watchstore.com` (from .env: `ADMIN_EMAIL`)
- **Password**: `Admin@123456` (from .env: `ADMIN_PASSWORD`)
- **Name**: Admin User (from .env: `ADMIN_NAME`)

Run seeder to create/update admin:
```bash
php artisan db:seed --class=AdminSeeder
```

---

## Public Routes (No Authentication Required)

### Authentication
- `POST /api/login` - Login
- `POST /api/register` - Register step 1 (send OTP)
- `POST /api/register/verify` - Register step 2 (verify OTP)
- `POST /api/forgot-password/send-otp` - Send password reset OTP
- `POST /api/forgot-password/reset` - Reset password with OTP
- `GET /api/auth/google` - Google OAuth redirect
- `GET /api/auth/google/callback` - Google OAuth callback

### Products Catalog (Read-Only)
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get category details
- `GET /api/brands` - List all brands
- `GET /api/brands/{id}` - Get brand details
- `GET /api/products` - List all products (with filters)
- `GET /api/products/{id}` - Get product details

---

## Authenticated Routes (Require Login)

### User Profile
- `POST /api/logout` - Logout
- `GET /api/me` - Get current user info
- `POST /api/refresh` - Refresh token
- `GET /api/user` - Get user data

### Shopping Cart (USER & ADMIN)
- `GET /api/cart` - Get user's cart
- `POST /api/cart/items` - Add item to cart
- `PUT /api/cart/items/{id}` - Update cart item quantity
- `DELETE /api/cart/items/{id}` - Remove item from cart
- `DELETE /api/cart/clear` - Clear entire cart

### Orders (USER & ADMIN)
- `GET /api/orders` - Get user's orders
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details
- `PUT /api/orders/{id}/cancel` - Cancel order

---

## Admin Only Routes (Require `role:admin`)

### Category Management
- `POST /api/categories` - Create category
- `PUT /api/categories/{id}` - Update category
- `DELETE /api/categories/{id}` - Delete category

### Brand Management
- `POST /api/brands` - Create brand
- `PUT /api/brands/{id}` - Update brand
- `DELETE /api/brands/{id}` - Delete brand

### Product Management
- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

---

## Route Protection Summary

| Route Group | Authentication | Role Required |
|-------------|---------------|---------------|
| Public Auth | ❌ No | - |
| Public Catalog | ❌ No | - |
| User Profile | ✅ Yes | USER/ADMIN |
| Shopping Cart | ✅ Yes | USER/ADMIN |
| Orders | ✅ Yes | USER/ADMIN |
| Category CRUD | ✅ Yes | **ADMIN** |
| Brand CRUD | ✅ Yes | **ADMIN** |
| Product CRUD | ✅ Yes | **ADMIN** |

---

## Testing Admin Access

### 1. Login as Admin
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@watchstore.com",
    "password": "Admin@123456"
  }'
```

### 2. Use Token in Requests
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "name": "New Product",
    ...
  }'
```

### 3. Check User Role
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Response should show:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@watchstore.com",
      "role": "ADMIN",
      ...
    }
  }
}
```

---

## Error Responses

### 401 Unauthorized (No Token)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden (Wrong Role)
```json
{
  "success": false,
  "message": "Unauthorized. Required role: ADMIN"
}
```
