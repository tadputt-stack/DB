# OnlySport - Cleats Store Application

ระบบร้านค้าจำหน่ายรองเท้าสตั๊ดออนไลน์ครบวงจร (Flutter Mobile & Web Application พร้อมระบบ Back Office จัดการหลังบ้านด้วย PHP & MySQL)

## จัดทำโดย
- 681310301 ธีรวุฒิ ทัดเพชร
- 681310498 จิรพัฒน์ บุณยะหุตานนท์

---

## 📁 Project Structure

```text
DB/
├── backend/                        # Backend Services & Admin Panel (PHP 8.2 & MySQL)
│   ├── admin/                      # PHP Back Office Dashboard, REST APIs & UI
│   │   ├── api_products.php        # REST API: ดึงข้อมูลสินค้าและตัวกรองตามซีรีส์
│   │   ├── api_orders.php          # REST API: สร้างคำสั่งซื้อและดึงประวัติการสั่งซื้อ
│   │   ├── db_conn.php             # ตัวเชื่อมต่อฐานข้อมูลอัตโนมัติ (Multi-Host Fallback)
│   │   ├── index.php               # แดชบอร์ดภาพรวม ยอดขายรวม ออเดอร์ และ 5 อันดับสินค้าขายดี
│   │   ├── menu.php                # แคตตาล็อกแสดงรายการสินค้าทั้งหมด (รองรับ Card / Table View)
│   │   ├── add_new.php             # แบบฟอร์มเพิ่มสินค้าสตั๊ดใหม่เข้าสู่ระบบ
│   │   ├── manage.php              # หน้ารวมจัดการสินค้า (แก้ไข, ลบ, ปุ่มทางลัดดูรีวิวสินค้า)
│   │   ├── edit.php                # แบบฟอร์มแก้ไขรายละเอียดสินค้า
│   │   ├── delete.php              # ระบบลบสินค้าออกจากฐานข้อมูล
│   │   ├── orders.php              # ระบบจัดการคำสั่งซื้อ อัปเดตสถานะ (Pending/Processing/Shipped/etc.)
│   │   ├── ratings.php             # หน้าแสดงรายการให้คะแนนและรีวิวสินค้า (Product Ratings & Reviews)
│   │   ├── users.php               # หน้าจัดการรายชื่อผู้ใช้งานระบบ (User Management + CRUD + Search + Pagination)
│   │   ├── navbar.php              # แถบเมนูนำทางส่วนบน (Global Navigation Bar Component)
│   │   ├── style.css               # ระบบดีไซน์ OnlySport Admin (Material Red & Dark Slate Theme)
│   │   └── .htaccess               # กฎ Apache URL Rewrite และการตั้งค่า CORS Headers
│   ├── Dockerfile                  # คอนฟิก Docker สำหรับ PHP 8.2 Apache
│   ├── docker-compose.yml          # คอนฟิก Services (MySQL 8.0, phpMyAdmin, Apache Web Server)
│   ├── database_schema.sql         # สคีมาฐานข้อมูล 6 ตาราง (series, products, orders, order_items, product_ratings, users)
│   ├── seed_products.sql           # ข้อมูลเริ่มต้นรองเท้าสตั๊ด Adidas ซีรีส์ Predator, F50, Copa (50+ รายการ)
│   └── seed_ratings.sql            # ข้อมูลรีวิวและคะแนนสินค้าตัวอย่าง (56 รีวิว)
│
├── mobile_app/                     # แอปพลิเคชันฝั่งผู้ใช้ (Flutter Mobile & Web)
│   ├── lib/                        # ซอร์สโค้ดภาษา Dart
│   │   ├── models/                 # Data Models (Product, Order, OrderItemModel, UserProfile)
│   │   ├── providers/              # ระบบจัดการ State (ProductProvider, CartProvider, OrderProvider, UserProvider)
│   │   ├── screens/                # หน้าจอ UI ต่างๆ
│   │   │   ├── main_screen.dart    # โครงสร้างหน้าหลักและ Bottom Navigation Bar
│   │   │   ├── home_screen.dart    # หน้าแรก แสดงแบนเนอร์ สินค้าแนะนำ และซีรีส์ยอดนิยม
│   │   │   ├── menu_screen.dart    # หน้ารายการสินค้าทั้งหมด พร้อมตัวกรองตามซีรีส์
│   │   │   ├── product_detail_screen.dart # หน้ารายละเอียดสินค้า เลือกขนาดรองเท้า (Size) และเพิ่มลงตะกร้า
│   │   │   ├── cart_screen.dart    # หน้าตะกร้าสินค้า ปรับจำนวน สรุปยอด และยืนยันการสั่งซื้อ
│   │   │   └── profile_screen.dart # หน้าโปรไฟล์ผู้ใช้ จัดการที่อยู่จัดส่ง และดูประวัติการสั่งซื้อ
│   │   ├── widgets/                # UI Components (ProductCard, CategoryChip ฯลฯ)
│   │   ├── services/               # คลาส ApiService เชื่อมต่อ REST API (รองรับทั้ง Web & Android Emulator)
│   │   └── main.dart               # จุดเริ่มต้นของแอปพลิเคชัน (App Entry Point & MultiProvider Theme)
│   ├── android/                    # โครงการ Android Native
│   ├── web/                        # คอนฟิกและทรัพยากรสำหรับ Flutter Web
│   ├── pubspec.yaml                # รายการ Packages และ Dependencies (provider, http, etc.)
│   └── analysis_options.yaml       # กฎการตรวจสอบโค้ด Dart Analyzer
│
├── .gitignore                      # กฎละเว้นการติดตามไฟล์สำหรับ Git
└── refactor_project.ps1            # สคริปต์ PowerShell สำหรับจัดระเบียบโครงสร้างโฟลเดอร์
```

---

##  โครงสร้างฐานข้อมูล (Database Schema)

ฐานข้อมูล `onlysport` ประกอบด้วย 6 ตารางหลัก:
1. **`series`**: หมวดหมู่ซีรีส์ของรองเท้าสตั๊ด (Predator, F50, Copa)
2. **`products`**: ข้อมูลรองเท้าสตั๊ด (ชื่อรุ่น, รูปภาพ CDN, ราคา, ซีรีส์, ประเภทชุดปุ่ม FG/TF/SG/MG, รายละเอียดสเปก)
3. **`orders`**: ข้อมูลคำสั่งซื้อ (รหัสคำสั่งซื้อ, ข้อมูลผู้สั่ง, ยอดรวมเงิน, สถานะคำสั่งซื้อ, วันที่ทำรายการ)
4. **`order_items`**: รายการสินค้าย่อยในแต่ละคำสั่งซื้อ (ไซส์, จำนวน, ราคาต่อหน่วย, ยอดรวม)
5. **`product_ratings`**: รายการรีวิวและความพึงพอใจต่อสินค้า (คะแนนดาว 1-5, ข้อความรีวิว, ชื่อผู้รีวิว)
6. **`users`**: รายชื่อและข้อมูลผู้ใช้งานระบบ (Username, ชื่อ-นามสกุล, อีเมล, เบอร์โทรศัพท์, วันที่สร้าง)

---

##  Getting Started

### 1. การติดตั้งและเริ่มต้นระบบ Backend (Docker)

1. เข้าไปยังโฟลเดอร์ `backend`:
   ```bash
   cd backend
   ```
2. สั่งเริ่มการทำงานของ Docker Containers:
   ```bash
   docker compose up -d
   ```
3. พอร์ตและการเข้าใช้งาน Services:
   - **Admin Back Office & REST API:** [http://localhost:8000](http://localhost:8000)
     - แดชบอร์ดภาพรวม: `http://localhost:8000/index.php`
     - รายการสินค้าทั้งหมด: `http://localhost:8000/menu.php`
     - จัดการสินค้า (แก้ไข/ลบ): `http://localhost:8000/manage.php`
     - เพิ่มสินค้าใหม่: `http://localhost:8000/add_new.php`
     - รายการคำสั่งซื้อ: `http://localhost:8000/orders.php`
     - รายการรีวิวสินค้า: `http://localhost:8000/ratings.php`
     - จัดการผู้ใช้งาน: `http://localhost:8000/users.php`
   - **phpMyAdmin:** [http://localhost:8080](http://localhost:8080)
   - **MySQL Database Server:** `localhost:3307` (ภายในคอนเทนเนอร์: `3306`, User: `root`, Password: `password`, Database: `onlysport`)

4. (ตัวเลือกเสริม) นำเข้าข้อมูลสินค้าและรีวิวตั้งต้น:
   ```bash
   # นำเข้าข้อมูลสินค้า 50+ รายการ
   docker exec -i onlysport_db mysql -u root -ppassword onlysport < seed_products.sql

   # นำเข้าข้อมูลรีวิวสินค้า 56 รายการ
   docker exec -i onlysport_db mysql -u root -ppassword onlysport < seed_ratings.sql
   ```

---

### 2. การติดตั้งและเปิดใช้งาน Frontend (Flutter Application)

1. เข้าไปยังโฟลเดอร์ `mobile_app`:
   ```bash
   cd mobile_app
   ```
2. ดาวน์โหลดแพ็กเกจ Dependencies:
   ```bash
   flutter pub get
   ```
3. สั่งรันแอปพลิเคชัน:
   - **สำหรับเบราว์เซอร์ Chrome (Web):**
     ```bash
     flutter run -d chrome
     ```
   - **สำหรับ Android Emulator:**
     ```bash
     flutter run -d emulator
     ```
