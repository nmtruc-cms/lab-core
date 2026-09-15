# Lab-core

Lab-core là ứng dụng quản lý phòng thí nghiệm được xây dựng bằng PHP và CodeIgniter 4 theo kiến trúc module. Dự án hiện tập trung vào nền tảng quản trị dùng chung (Core) và quản lý kho, vật tư, mua hàng (IMS), với giao diện hỗ trợ tiếng Việt và tiếng Anh.

## Các module

| Module | Phạm vi | Trạng thái trong mã nguồn |
| --- | --- | --- |
| **Core** | Xác thực, phân quyền, người dùng, phòng ban, hồ sơ công ty và đăng ký module | Đã có triển khai |
| **IMS** | Quản lý kho, vật tư, nhà cung cấp, yêu cầu mua hàng, đơn đặt hàng và công thức pha chế | Đã có triển khai, bật mặc định |
| **LIMS** | Định hướng quản lý mẫu, quy trình thử nghiệm và kết quả | Mới có cấu hình module, tắt mặc định |
| **QMS** | Định hướng quản lý tài liệu và quy trình chất lượng | Mới có cấu hình module, tắt mặc định |

### Chức năng hiện có

- **Quản trị:** đăng nhập bằng CodeIgniter Shield, quản lý tài khoản, xem vai trò và quyền, quản lý phòng ban và thông tin công ty.
- **Danh mục kho:** vật tư, thương hiệu, nhóm vật tư, đơn vị tính và vị trí lưu trữ.
- **Tồn kho:** quản lý lô, giao dịch kho, tài liệu đính kèm và xuất nhãn lô dạng PDF.
- **Nhà cung cấp:** hồ sơ, phân loại, liên kết thương hiệu, tài liệu và đánh giá theo checklist.
- **Mua hàng:** lập và duyệt yêu cầu mua, quản lý đơn đặt hàng, chi tiết hàng hóa, tệp đính kèm và xuất đơn PDF.
- **Pha chế:** quản lý công thức, thành phần, tài liệu và nhãn PDF.

## Công nghệ và yêu cầu

Các ràng buộc phiên bản dưới đây lấy từ [composer.json](composer.json); phiên bản cài đặt cụ thể được cố định trong `composer.lock`.

| Thành phần | Phiên bản / vai trò |
| --- | --- |
| PHP | `^8.2` |
| CodeIgniter | `^4.7` |
| CodeIgniter Shield | `^1.3` — xác thực và phân quyền |
| Dompdf | `^3.1` — xuất PDF |
| PHPUnit | `^10.5.16` — kiểm thử |
| Cơ sở dữ liệu | MySQL/MariaDB qua driver `MySQLi` theo cấu hình mặc định |
| Giao diện | PHP views, Bootstrap, Font Awesome, CSS và JavaScript trong `public/assets/` |

Môi trường cần có Composer và các extension PHP `intl`, `mbstring`, `mysqli`, `dom`, `fileinfo`. Bật thêm `gd` để xử lý ảnh khi xuất PDF và `sqlite3` để chạy kiểm thử database theo cấu hình hiện tại. Thư mục `writable/` phải cho phép tiến trình PHP ghi dữ liệu.

## Cài đặt trên máy phát triển

Chạy các lệnh sau từ thư mục gốc của repository. Hướng dẫn giả định bạn đã có PHP, Composer và một máy chủ MySQL/MariaDB đang chạy.

### 1. Cài thư viện

```sh
composer install
composer check-platform-reqs
```

### 2. Tạo cấu hình môi trường

Nếu chưa có `.env`, sao chép từ file mẫu `env`.

**Windows PowerShell:**

```powershell
Copy-Item env .env
```

**Linux/macOS:**

```sh
cp env .env
```

Tạo một database rỗng, ví dụ `lab_core`, rồi chỉnh `.env` theo môi trường của bạn. Bỏ dấu `#` ở đầu những dòng cấu hình cần sử dụng:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = lab_core
database.default.username = your_db_user
database.default.password = 'your_db_password'
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Thay `your_db_user` và `your_db_password` bằng thông tin kết nối thực tế. Giữ dấu `/` cuối `app.baseURL`; không đưa `.env` chứa thông tin truy cập vào Git.

### 3. Tạo bảng dữ liệu

```sh
php spark migrate --all
```

Tùy chọn `--all` chạy migration ở tất cả namespace, bao gồm các thư viện xác thực, settings và các module của Lab-core. Migration của Core cần bảng `users` của Shield, vì vậy cần chạy đầy đủ migration khi cài mới.

### 4. Khởi tạo quyền và tài khoản

**Để dùng thử trên database phát triển mới**, chạy seeder demo:

```sh
php spark db:seed 'Modules\Core\Database\Seeds\AuthDemoUsersSeeder'
```

Seeder này khởi tạo cả vai trò, quyền và tám tài khoản:

| Tài khoản | Nhóm quyền |
| --- | --- |
| `super_user@lab-core.com` | `super_user` |
| `lab_manager@lab-core.com` | `lab_manager` |
| `director@lab-core.com` | `director` |
| `qa@lab-core.com` | `qa` |
| `sales@lab-core.com` | `sales` |
| `service@lab-core.com` | `service` |
| `lab_technician@lab-core.com` | `lab_technician` |
| `lab_assistant@lab-core.com` | `lab_assistant` |

Mật khẩu demo chung: `123456`. Chỉ sử dụng các tài khoản này trong môi trường phát triển.

**Để khởi tạo tài khoản riêng**, chạy seeder vai trò rồi tạo quản trị viên qua CLI của Shield:

```sh
php spark db:seed 'Modules\Core\Database\Seeds\AuthRoleBaseSeeder'
php spark shield:user create -n admin -e admin@example.com -g super_user
php spark shield:user activate -n admin
```

Thay email ví dụ bằng email của bạn và nhập mật khẩu khi CLI yêu cầu.

> Cả hai seeder đều ghi đè cấu hình vai trò và quyền trong bảng `settings`. Seeder demo còn đặt lại mật khẩu, nhóm và quyền trực tiếp của các tài khoản demo đã tồn tại. Không chạy lại trên hệ thống đã tùy chỉnh phân quyền nếu chưa chủ động muốn đặt lại các dữ liệu này.

### 5. Chạy ứng dụng

```sh
php spark serve
```

Mở `http://localhost:8080/` và đăng nhập. Có thể dùng tài khoản `super_user@lab-core.com` nếu đã chạy seeder demo.

### Sử dụng Apache/XAMPP

Cấu hình VirtualHost có `DocumentRoot` trỏ đến thư mục `public/`, ví dụ `C:/xampp/htdocs/lab-core/public`. Bật `mod_rewrite` và cho phép `.htaccess` với `AllowOverride All` trong cấu hình thư mục đó, rồi đặt `app.baseURL` khớp địa chỉ VirtualHost.

Web server chỉ nên phục vụ nội dung từ `public/` để các thư mục mã nguồn, `.env` và dữ liệu trong `writable/` không bị truy cập trực tiếp qua web.

## Các trang chính

Các đường dẫn được tính từ `app.baseURL`; tài khoản cần có quyền tương ứng để truy cập trang quản trị và IMS.

| Đường dẫn | Nội dung |
| --- | --- |
| `/login` | Đăng nhập |
| `/dashboard` | Trang điều hướng sau đăng nhập |
| `/admin` | Tổng quan quản trị |
| `/admin/users` | Quản lý người dùng |
| `/admin/roles` | Xem vai trò và quyền |
| `/admin/departments` | Quản lý phòng ban |
| `/admin/company-profile` | Hồ sơ công ty |
| `/ims` | Tổng quan kho |
| `/ims/master-data` | Danh mục dùng chung của IMS |
| `/ims/items` | Vật tư |
| `/ims/lots` | Lô tồn kho |
| `/ims/transactions` | Giao dịch kho |
| `/ims/locations` | Vị trí lưu trữ |
| `/ims/suppliers` | Nhà cung cấp |
| `/ims/requests` | Yêu cầu mua hàng |
| `/ims/purchase-orders` | Đơn đặt hàng |
| `/ims/formulations` | Công thức pha chế |

## Cấu trúc dự án

```text
lab-core/
├── app/                    # Cấu hình ứng dụng, hàm dùng chung, layout và ngôn ngữ
├── Modules/
│   ├── Core/               # Quản trị, xác thực, phân quyền và registry module
│   ├── IMS/                # Nghiệp vụ kho, mua hàng và pha chế
│   ├── LIMS/Config/        # Khung cấu hình LIMS
│   └── QMS/Config/         # Khung cấu hình QMS
├── public/                 # Web root, index.php và tài nguyên giao diện
├── Template/               # Các tệp mẫu của dự án
├── tests/                  # Bộ kiểm thử PHPUnit
├── writable/               # Cache, log, session và tệp tải lên
├── env                     # Mẫu cấu hình môi trường
├── composer.json           # Khai báo thư viện và PSR-4 autoload
├── composer.lock           # Phiên bản thư viện đã khóa
└── spark                   # Công cụ dòng lệnh CodeIgniter
```

### Quy ước phát triển module

- Đặt controller, model, view, route và migration nghiệp vụ trong module tương ứng.
- Các module nghiệp vụ phụ thuộc vào Core; chức năng cần dùng chung nên được cung cấp qua Core.
- Danh sách module nằm trong [Registry.php](Modules/Core/Config/Registry.php). Mỗi module khai báo metadata, phụ thuộc và trạng thái mặc định tại `Config/Module.php`; registry có thể đọc trạng thái ghi đè từ bảng `core_modules`.
- Khai báo quyền tại [PermissionCatalog.php](Modules/Core/Config/PermissionCatalog.php), vai trò mặc định tại [RoleTemplates.php](Modules/Core/Config/RoleTemplates.php). Kiểm tra quyền qua Shield, ví dụ `auth()->user()->can('ims.stock.receive')`.
- Khi thêm namespace module, cập nhật PSR-4 trong `composer.json` và chạy `composer dump-autoload`.

## Kiểm thử

Chạy bộ kiểm thử không thu thập coverage:

```sh
composer test -- --no-coverage
```

Để thu thập coverage, cấu hình Xdebug với `xdebug.mode=coverage` rồi chạy:

```sh
composer test
```

Cấu hình nằm trong [phpunit.xml.dist](phpunit.xml.dist). Nhóm database `tests` mặc định dùng SQLite trong bộ nhớ (`:memory:`) theo [Database.php](app/Config/Database.php); báo cáo được ghi vào `build/logs/`.

Bộ test hiện chủ yếu là các bài kiểm thử mẫu về health, session và database của ứng dụng khởi tạo. Cấu hình coverage hiện chỉ bao gồm `app/`, chưa bao gồm `Modules/`, nên kết quả không phản ánh độ bao phủ nghiệp vụ Core/IMS.

## Tài liệu trong repository

- [Định hướng kiến trúc Core](Modules/Core/README.md)
- [Định hướng kiến trúc IMS](Modules/IMS/README.md)
- [Thiết kế vai trò và phân quyền](Modules/Core/README-Roles.md)

## Giấy phép

Repository chứa giấy phép [MIT](LICENSE). Các thư viện phụ thuộc có giấy phép riêng.
