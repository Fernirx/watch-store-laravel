# API Services Documentation

Tài liệu hướng dẫn sử dụng các services trong frontend.

## Import Services

```javascript
// Import từng service riêng lẻ
import authService from '@/services/authService';
import productService from '@/services/productService';
import categoryService from '@/services/categoryService';
import brandService from '@/services/brandService';
import cartService from '@/services/cartService';
import orderService from '@/services/orderService';

// Hoặc import tất cả cùng lúc
import {
  authService,
  productService,
  categoryService,
  brandService,
  cartService,
  orderService
} from '@/services';
```

---

## 1. AuthService

### Public Endpoints

#### `login(email, password)`
Đăng nhập với email và password.
```javascript
const result = await authService.login('user@example.com', 'password123');
// Tự động lưu token và user vào localStorage
```

#### `sendRegisterOtp(name, email, password, password_confirmation)`
Bước 1: Đăng ký và gửi OTP qua email.
```javascript
const result = await authService.sendRegisterOtp(
  'John Doe',
  'john@example.com',
  'password123',
  'password123'
);
```

#### `verifyRegisterOtp(email, otp)`
Bước 2: Xác thực OTP để hoàn tất đăng ký.
```javascript
const result = await authService.verifyRegisterOtp('john@example.com', '123456');
// Tự động lưu token và user vào localStorage
```

#### `sendForgotPasswordOtp(email)`
Gửi OTP để reset mật khẩu.
```javascript
const result = await authService.sendForgotPasswordOtp('user@example.com');
```

#### `resetPassword(email, otp, password, password_confirmation)`
Reset mật khẩu với OTP.
```javascript
const result = await authService.resetPassword(
  'user@example.com',
  '123456',
  'newPassword123',
  'newPassword123'
);
```

#### `googleLogin()`
Chuyển hướng đến Google OAuth.
```javascript
authService.googleLogin();
```

### Protected Endpoints (Require Authentication)

#### `logout()`
Đăng xuất.
```javascript
await authService.logout();
// Tự động xóa token và user khỏi localStorage
```

#### `getCurrentUser()`
Lấy thông tin user hiện tại từ server.
```javascript
const user = await authService.getCurrentUser();
```

#### `refreshToken()`
Làm mới access token.
```javascript
const result = await authService.refreshToken();
// Tự động cập nhật token mới vào localStorage
```

### Helper Methods

#### `isAuthenticated()`
Kiểm tra đã đăng nhập chưa.
```javascript
const isLoggedIn = authService.isAuthenticated();
```

#### `getUser()`
Lấy user từ localStorage.
```javascript
const user = authService.getUser();
```

#### `isAdmin()`
Kiểm tra có phải admin không.
```javascript
const isAdmin = authService.isAdmin();
```

---

## 2. ProductService

### Public Endpoints

#### `getProducts(params)`
Lấy danh sách sản phẩm với filters và pagination.
```javascript
const result = await productService.getProducts({
  category_id: 1,
  brand_id: 2,
  min_price: 100,
  max_price: 5000,
  gender: 'male', // 'male' | 'female' | 'unisex'
  is_featured: true,
  search: 'Rolex',
  sort_by: 'price', // 'price' | 'created_at' | 'name'
  sort_order: 'asc', // 'asc' | 'desc'
  per_page: 12,
  page: 1
});
```

#### `getProduct(id)`
Lấy chi tiết sản phẩm.
```javascript
const result = await productService.getProduct(1);
```

#### `getCategories()`
Lấy tất cả danh mục.
```javascript
const result = await productService.getCategories();
```

#### `getCategory(id)`
Lấy chi tiết danh mục.
```javascript
const result = await productService.getCategory(1);
```

#### `getBrands()`
Lấy tất cả thương hiệu.
```javascript
const result = await productService.getBrands();
```

#### `getBrand(id)`
Lấy chi tiết thương hiệu.
```javascript
const result = await productService.getBrand(1);
```

### Admin Endpoints (Require Auth + Admin Role)

#### `createProduct(productData)`
Tạo sản phẩm mới.
```javascript
const formData = new FormData();
formData.append('category_id', 1);
formData.append('brand_id', 2);
formData.append('name', 'Rolex Submariner');
formData.append('description', 'Luxury watch...');
formData.append('price', 12000);
formData.append('sale_price', 10000);
formData.append('sku', 'RLX-SUB-001');
formData.append('stock_quantity', 10);
formData.append('gender', 'male');
formData.append('is_featured', true);
formData.append('is_active', true);

// Thêm nhiều ảnh
formData.append('images[]', imageFile1);
formData.append('images[]', imageFile2);

// Specifications
formData.append('case_material', 'Stainless Steel');
formData.append('strap_material', 'Oyster bracelet');
formData.append('movement_type', 'Automatic');
formData.append('water_resistance', '300m');
formData.append('dial_color', 'Black');
formData.append('case_diameter', '40mm');

const result = await productService.createProduct(formData);
```

#### `updateProduct(id, productData)`
Cập nhật sản phẩm.
```javascript
const formData = new FormData();
formData.append('name', 'Updated Name');
formData.append('price', 15000);
// ... other fields

const result = await productService.updateProduct(1, formData);
```

#### `deleteProduct(id)`
Xóa sản phẩm.
```javascript
const result = await productService.deleteProduct(1);
```

#### `createCategory(categoryData)`, `updateCategory(id, categoryData)`, `deleteCategory(id)`
CRUD operations cho danh mục.

#### `createBrand(brandData)`, `updateBrand(id, brandData)`, `deleteBrand(id)`
CRUD operations cho thương hiệu.

---

## 3. CategoryService

### Public Endpoints

#### `getCategories()`
Lấy tất cả danh mục.
```javascript
const result = await categoryService.getCategories();
```

#### `getCategory(id)`
Lấy chi tiết danh mục.
```javascript
const result = await categoryService.getCategory(1);
```

### Admin Endpoints

#### `createCategory(categoryData)`
Tạo danh mục mới.
```javascript
const formData = new FormData();
formData.append('name', 'Men Watches');
formData.append('slug', 'men-watches');
formData.append('description', 'Premium watches for men');
formData.append('image', imageFile);
formData.append('is_active', true);

const result = await categoryService.createCategory(formData);

// Hoặc truyền object (service sẽ tự convert thành FormData)
const result = await categoryService.createCategory({
  name: 'Men Watches',
  slug: 'men-watches',
  description: 'Premium watches for men',
  image: imageFile,
  is_active: true
});
```

#### `updateCategory(id, categoryData)`
Cập nhật danh mục.
```javascript
const result = await categoryService.updateCategory(1, {
  name: 'Updated Name',
  is_active: false
});
```

#### `deleteCategory(id)`
Xóa danh mục.
```javascript
const result = await categoryService.deleteCategory(1);
```

---

## 4. BrandService

### Public Endpoints

#### `getBrands()`
Lấy tất cả thương hiệu.
```javascript
const result = await brandService.getBrands();
```

#### `getBrand(id)`
Lấy chi tiết thương hiệu.
```javascript
const result = await brandService.getBrand(1);
```

### Admin Endpoints

#### `createBrand(brandData)`
Tạo thương hiệu mới.
```javascript
const result = await brandService.createBrand({
  name: 'Rolex',
  slug: 'rolex',
  description: 'Swiss luxury watchmaker',
  logo: logoFile,
  website: 'https://www.rolex.com',
  is_active: true
});
```

#### `updateBrand(id, brandData)`
Cập nhật thương hiệu.
```javascript
const result = await brandService.updateBrand(1, {
  name: 'Updated Name',
  website: 'https://newurl.com'
});
```

#### `deleteBrand(id)`
Xóa thương hiệu.
```javascript
const result = await brandService.deleteBrand(1);
```

---

## 5. CartService

Tất cả endpoints đều yêu cầu authentication.

#### `getCart()`
Lấy giỏ hàng hiện tại.
```javascript
const result = await cartService.getCart();
```

#### `addToCart(product_id, quantity)`
Thêm sản phẩm vào giỏ.
```javascript
const result = await cartService.addToCart(1, 2);
```

#### `updateCartItem(id, quantity)`
Cập nhật số lượng sản phẩm trong giỏ.
```javascript
const result = await cartService.updateCartItem(1, 5);
```

#### `removeCartItem(id)`
Xóa sản phẩm khỏi giỏ.
```javascript
const result = await cartService.removeCartItem(1);
```

#### `clearCart()`
Xóa toàn bộ giỏ hàng.
```javascript
const result = await cartService.clearCart();
```

---

## 6. OrderService

Tất cả endpoints đều yêu cầu authentication.

#### `getOrders()`
Lấy danh sách đơn hàng của user.
```javascript
const result = await orderService.getOrders();
```

#### `getOrder(id)`
Lấy chi tiết đơn hàng.
```javascript
const result = await orderService.getOrder(1);
```

#### `createOrder(orderData)`
Tạo đơn hàng mới.
```javascript
const result = await orderService.createOrder({
  shipping_address: '123 Main St, City',
  shipping_phone: '0123456789',
  payment_method: 'cod', // 'cod' | 'bank_transfer' | 'credit_card'
  notes: 'Please deliver before 5pm'
});
```

#### `cancelOrder(id)`
Hủy đơn hàng.
```javascript
const result = await orderService.cancelOrder(1);
```

---

## Error Handling

Tất cả services đều throw error nếu request thất bại. Nên sử dụng try-catch:

```javascript
try {
  const result = await productService.getProducts();
  console.log(result.data);
} catch (error) {
  if (error.response) {
    // Server trả về error response
    console.error(error.response.data.message);
    console.error(error.response.data.errors); // Validation errors
  } else {
    // Network error hoặc lỗi khác
    console.error(error.message);
  }
}
```

## Response Format

Tất cả API đều trả về format:

### Success Response
```javascript
{
  success: true,
  message: "Success message",
  data: { ... }
}
```

### Error Response
```javascript
{
  success: false,
  message: "Error message",
  errors: { ... } // Validation errors (nếu có)
}
```

## Authentication

- Token được tự động thêm vào header của mỗi request thông qua axios interceptor
- Khi token hết hạn (401), user sẽ tự động được redirect về trang login
- Token được lưu trong localStorage với key `token`
- User info được lưu trong localStorage với key `user`
