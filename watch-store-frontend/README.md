# Watch Store - Frontend (React + Vite)

Frontend cho hệ thống web bán đồng hồ cao cấp, được xây dựng với React và Vite.

## Tính năng

### Authentication
- ✅ Đăng nhập với email/password
- ✅ Đăng ký tài khoản với xác thực OTP qua email
- ✅ Đăng nhập với Google OAuth2
- ✅ Quên mật khẩu và đặt lại mật khẩu với OTP

### Sản phẩm
- ✅ Trang chủ với sản phẩm nổi bật
- ✅ Danh sách sản phẩm với bộ lọc theo danh mục và thương hiệu
- ✅ Chi tiết sản phẩm với hình ảnh, mô tả, giá
- ✅ Hiển thị giá sale và tính phần trăm giảm giá

### Giỏ hàng
- ✅ Thêm sản phẩm vào giỏ hàng
- ✅ Cập nhật số lượng sản phẩm
- ✅ Xóa sản phẩm khỏi giỏ hàng
- ✅ Xóa toàn bộ giỏ hàng

### Đơn hàng
- ✅ Checkout và tạo đơn hàng
- ✅ Xem danh sách đơn hàng
- ✅ Xem chi tiết đơn hàng
- ✅ Hủy đơn hàng

## Cấu trúc thư mục

```
src/
├── api/              # Cấu hình axios
├── components/       # React components
│   ├── common/       # Components dùng chung
│   └── layout/       # Layout components (Header, Footer)
├── contexts/         # React Context (Auth, Cart)
├── pages/            # Các trang
│   ├── auth/         # Trang đăng nhập, đăng ký, OTP, quên mật khẩu
│   ├── products/     # Trang sản phẩm
│   ├── cart/         # Trang giỏ hàng, checkout
│   └── orders/       # Trang đơn hàng
└── services/         # API services
```

## Cài đặt

```bash
cd watch-store-frontend
npm install
```

## Cấu hình

Tạo file `.env` (đã có sẵn):
```
VITE_API_URL=http://localhost:8000/api
```

## Chạy ứng dụng

### Development
```bash
npm run dev
```

Ứng dụng sẽ chạy tại: `http://localhost:5173`

### Build Production
```bash
npm run build
```

### Preview Production Build
```bash
npm run preview
```

## API Endpoints sử dụng

### Auth
- `POST /login` - Đăng nhập
- `POST /register` - Gửi OTP đăng ký
- `POST /register/verify` - Xác thực OTP và hoàn tất đăng ký
- `POST /forgot-password/send-otp` - Gửi OTP quên mật khẩu
- `POST /forgot-password/reset` - Đặt lại mật khẩu
- `GET /auth/google` - Redirect Google OAuth
- `GET /auth/google/callback` - Callback Google OAuth
- `POST /logout` - Đăng xuất
- `GET /me` - Lấy thông tin user

### Products
- `GET /products` - Danh sách sản phẩm
- `GET /products/:id` - Chi tiết sản phẩm
- `GET /categories` - Danh sách danh mục
- `GET /brands` - Danh sách thương hiệu

### Cart (Yêu cầu authentication)
- `GET /cart` - Lấy giỏ hàng
- `POST /cart/items` - Thêm sản phẩm
- `PUT /cart/items/:id` - Cập nhật số lượng
- `DELETE /cart/items/:id` - Xóa sản phẩm
- `DELETE /cart/clear` - Xóa toàn bộ giỏ

### Orders (Yêu cầu authentication)
- `GET /orders` - Danh sách đơn hàng
- `POST /orders` - Tạo đơn hàng
- `GET /orders/:id` - Chi tiết đơn hàng
- `PUT /orders/:id/cancel` - Hủy đơn hàng

## Tech Stack

- **React** - UI Library
- **Vite** - Build tool
- **React Router** - Routing
- **Axios** - HTTP client
- **Context API** - State management
- **CSS3** - Styling

## Tính năng nâng cao

- Protected Routes cho các trang yêu cầu đăng nhập
- Auto-redirect khi token hết hạn
- Cart counter realtime
- OTP countdown timer
- Responsive design
- Loading states
- Error handling

## Lưu ý

- Backend API phải chạy trước tại `http://localhost:8000`
- Cần cấu hình Google OAuth credentials trong backend
- Email OTP cần cấu hình MAIL trong backend Laravel
