# Hướng dẫn sửa lỗi SSL Certificate trên Windows

## ⚠️ Vấn đề

Khi upload ảnh lên Cloudinary trên Windows, gặp lỗi:

```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

## 🔍 Nguyên nhân

Windows không có sẵn file chứng chỉ SSL (cacert.pem) mà cURL cần để xác thực HTTPS connections.

## ✅ Giải pháp

### Bước 1: Tải file cacert.pem

Mở Command Prompt hoặc Terminal tại thư mục project:

```bash
cd P:\School\watch-store-laravel
curl -o cacert.pem https://curl.se/ca/cacert.pem
```

Hoặc tải thủ công:
1. Truy cập: https://curl.se/ca/cacert.pem
2. Save file vào thư mục project: `P:\School\watch-store-laravel\cacert.pem`

### Bước 2: Tìm file php.ini

```bash
php --ini
```

Output sẽ hiển thị đường dẫn, ví dụ:
```
Loaded Configuration File: C:\Users\hungp\AppData\Local\Programs\PHP\php.ini
```

### Bước 3: Cấu hình php.ini

Mở file `php.ini` bằng Notepad hoặc text editor.

**Tìm dòng (hoặc thêm vào cuối file):**

```ini
;curl.cainfo =
```

**Sửa thành (bỏ dấu ; và thêm đường dẫn):**

```ini
curl.cainfo = "P:\School\watch-store-laravel\cacert.pem"
```

**Nếu dùng OpenSSL, thêm luôn dòng này:**

```ini
openssl.cafile = "P:\School\watch-store-laravel\cacert.pem"
```

### Bước 4: Lưu và verify

**Lưu file php.ini**, sau đó kiểm tra:

```bash
php -r "echo 'curl.cainfo = ' . ini_get('curl.cainfo') . PHP_EOL;"
```

Output mong đợi:
```
curl.cainfo = P:\School\watch-store-laravel\cacert.pem
```

### Bước 5: Restart Laravel server

**Quan trọng:** Phải restart server để áp dụng config mới!

```bash
# Dừng server hiện tại: Ctrl + C

# Chạy lại:
php artisan serve
```

## 🧪 Test

### Test 1: Update sản phẩm KHÔNG có ảnh mới

1. Vào `/admin/products`
2. Click "Sửa" trên sản phẩm bất kỳ
3. Chỉ thay đổi giá: `41.240.000`
4. Click "Cập nhật"
5. **Mong đợi:** Thành công (không upload ảnh)

### Test 2: Update sản phẩm CÓ ảnh mới

1. Vào `/admin/products`
2. Click "Sửa" trên sản phẩm
3. Chọn ảnh mới
4. Click "Cập nhật"
5. **Mong đợi:** Upload lên Cloudinary thành công

### Test 3: Tạo sản phẩm mới

1. Vào `/admin/products/create`
2. Nhập thông tin + chọn ảnh
3. Click "Tạo mới"
4. **Mong đợi:** Upload thành công

## 🐛 Troubleshooting

### Vẫn lỗi SSL sau khi config?

**Giải pháp 1: Kiểm tra đường dẫn**

```bash
# Đảm bảo file tồn tại
dir "P:\School\watch-store-laravel\cacert.pem"
```

**Giải pháp 2: Thử đường dẫn tuyệt đối khác**

Sửa php.ini thành:

```ini
curl.cainfo = "C:/Users/hungp/cacert.pem"
openssl.cafile = "C:/Users/hungp/cacert.pem"
```

Và copy file:
```bash
copy "P:\School\watch-store-laravel\cacert.pem" "C:\Users\hungp\cacert.pem"
```

**Giải pháp 3: Fallback code (đã có sẵn)**

Nếu vẫn không được, code trong `CloudinaryService.php` sẽ tự động:
- Catch SSL error
- Retry bằng HTTP thay vì HTTPS (chỉ dùng development)

### File php.ini không tìm thấy?

```bash
# Tìm tất cả php.ini
dir /s /b C:\php.ini
dir /s /b "C:\Program Files\PHP\php.ini"
dir /s /b "%USERPROFILE%\php.ini"
```

### Không có quyền sửa php.ini?

Chạy editor với quyền Administrator:
1. Right-click Notepad
2. Chọn "Run as administrator"
3. Mở file php.ini
4. Sửa và Save

## 📝 Giải thích kỹ thuật

### cacert.pem là gì?

- File chứa danh sách các Certificate Authorities (CAs) được tin cậy
- Dùng để xác thực SSL/TLS certificates của website
- Mozilla cung cấp và cập nhật thường xuyên

### Tại sao Windows thiếu file này?

- Linux/Mac có sẵn trong hệ thống (`/etc/ssl/certs/`)
- Windows không có, cần cài thủ công

### Production setup

Trên production server, nên:
1. Đặt cacert.pem tại vị trí cố định: `/etc/ssl/certs/cacert.pem`
2. Config php.ini global
3. Update định kỳ (mỗi 3-6 tháng)

```bash
# Cron job update cacert.pem (Linux)
0 0 1 * * curl -o /etc/ssl/certs/cacert.pem https://curl.se/ca/cacert.pem
```

## 🔐 Bảo mật

### ⚠️ KHÔNG BAO GIỜ

```php
// KHÔNG làm thế này trên production:
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
```

### ✅ Nên làm

- Dùng cacert.pem chính thức từ https://curl.se/ca/cacert.pem
- Config php.ini đúng cách
- Update cacert.pem định kỳ

## 📚 Tài liệu tham khảo

- **cURL CA Extract**: https://curl.se/docs/caextract.html
- **PHP cURL Options**: https://www.php.net/manual/en/function.curl-setopt.php
- **Cloudinary PHP SDK**: https://cloudinary.com/documentation/php_integration

## ✅ Checklist hoàn thành

- [ ] Tải cacert.pem về thư mục project
- [ ] Tìm file php.ini
- [ ] Thêm `curl.cainfo` vào php.ini
- [ ] Thêm `openssl.cafile` vào php.ini (nếu cần)
- [ ] Lưu file php.ini
- [ ] Verify config bằng lệnh `php -r`
- [ ] Restart Laravel server
- [ ] Test upload ảnh thành công

## 🎉 Kết quả

Sau khi hoàn tất, bạn có thể:
- ✅ Upload ảnh sản phẩm lên Cloudinary
- ✅ Upload ảnh danh mục
- ✅ Upload logo thương hiệu
- ✅ Không còn SSL certificate error

---

**Lưu ý:** Nếu làm theo hướng dẫn mà vẫn lỗi, hãy:
1. Check Laravel log: `storage/logs/laravel.log`
2. Check browser console (F12)
3. Paste full error message để được hỗ trợ
