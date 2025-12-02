# Hướng dẫn cập nhật format giá VND

## Vấn đề đã sửa

Trước đây database sử dụng `decimal(10, 2)` cho giá, chỉ lưu được tối đa ~99 triệu VND và có 2 chữ số thập phân (không phù hợp với VND).

Đã cập nhật sang `decimal(15, 0)` để:
- Lưu được giá lên đến 999,999,999,999,999 VND
- Không có chữ số thập phân (VND không dùng xu)

## Các thay đổi

### Backend (Laravel)

1. **Migration mới**: `database/migrations/2025_01_02_000001_update_products_price_precision.php`
   - Thay đổi `price` và `sale_price` từ `decimal(10, 2)` → `decimal(15, 0)`

2. **Model**: `app/Models/Product.php`
   - Cập nhật casts từ `decimal:2` → `decimal:0`

### Frontend (React)

1. **Utility Functions**: `src/utils/formatPrice.js`
   - `formatPrice()`: Format số thành chuỗi VND (41.240.000 ₫)
   - `parsePrice()`: Parse chuỗi VND về số
   - `formatPriceInput()`: Format input khi người dùng nhập
   - `calculateDiscount()`: Tính % giảm giá

2. **ProductForm**: `src/pages/admin/ProductForm.jsx`
   - Input giá tự động format theo VND khi gõ
   - Hiển thị preview giá bên dưới input
   - Chấp nhận cả định dạng: 41240000 hoặc 41.240.000

## Cách chạy migration

### Bước 1: Cài đặt doctrine/dbal (cần cho alter column)

```bash
cd P:\School\watch-store-laravel
composer require doctrine/dbal
```

### Bước 2: Chạy migration

```bash
php artisan migrate
```

**Output mong đợi:**
```
Running migrations.
2025_01_02_000001_update_products_price_precision ...................... DONE
```

### Bước 3: Kiểm tra database

```bash
php artisan tinker
```

Trong tinker:
```php
// Kiểm tra structure
DB::select('DESCRIBE products');

// Kiểm tra dữ liệu sản phẩm
App\Models\Product::first();
```

## Test thử

### Test 1: Tạo sản phẩm mới với giá VND

1. Vào `/admin/products/create`
2. Nhập giá: `41.240.000` hoặc `41240000`
3. Nhập giá khuyến mãi: `35.000.000`
4. Submit form
5. Kiểm tra trong database và danh sách sản phẩm

### Test 2: Sửa sản phẩm hiện có

1. Vào `/admin/products`
2. Click "Sửa" trên một sản phẩm
3. Thay đổi giá thành `50.000.000`
4. Save và kiểm tra

### Test 3: Hiển thị giá trên frontend

1. Vào trang danh sách sản phẩm `/products`
2. Kiểm tra format giá: `41.240.000 ₫`
3. Vào chi tiết sản phẩm `/products/:id`
4. Kiểm tra giá gốc và giá khuyến mãi

## Lưu ý quan trọng

⚠️ **Backup database trước khi chạy migration!**

```bash
# MySQL
mysqldump -u username -p database_name > backup_before_price_update.sql

# SQLite
cp database/database.sqlite database/database.sqlite.backup
```

## Rollback nếu cần

Nếu có vấn đề, rollback migration:

```bash
php artisan migrate:rollback --step=1
```

## Các file đã thay đổi

### Backend
- ✅ `database/migrations/000004_create_products_table.php`
- ✅ `database/migrations/2025_01_02_000001_update_products_price_precision.php` (MỚI)
- ✅ `app/Models/Product.php`

### Frontend
- ✅ `src/utils/formatPrice.js` (MỚI)
- ✅ `src/pages/admin/ProductForm.jsx`

### Các file khác đã sử dụng format VND đúng (không cần thay đổi)
- `src/pages/admin/Products.jsx`
- `src/pages/admin/Orders.jsx`
- `src/pages/admin/OrderDetail.jsx`
- `src/pages/products/ProductList.jsx`
- `src/pages/products/ProductDetail.jsx`
- `src/pages/cart/Cart.jsx`
- `src/pages/cart/Checkout.jsx`
- `src/pages/orders/OrderList.jsx`
- `src/pages/orders/OrderDetail.jsx`

## Ví dụ format giá

| Input | Display | Database Value |
|-------|---------|----------------|
| 41240000 | 41.240.000 ₫ | 41240000 |
| 41.240.000 | 41.240.000 ₫ | 41240000 |
| 1500000 | 1.500.000 ₫ | 1500000 |
| 999999999999999 | 999.999.999.999.999 ₫ | 999999999999999 |

## Support

Nếu gặp lỗi khi chạy migration:
1. Kiểm tra đã cài doctrine/dbal chưa
2. Kiểm tra database connection
3. Backup và thử lại
4. Xem log: `storage/logs/laravel.log`
