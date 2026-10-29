# OnlySport - E-Commerce Platform

This repository is organized into a clean decoupled architecture separating the **Backend (PHP & MySQL Dockerized Services)** and the **Frontend (Flutter Mobile & Web Application)**.

---

## 📁 Project Structure

```text
DB/
├── backend/                        # Backend Services & Admin Panel
│   ├── admin/                      # PHP Admin Dashboard, REST APIs, & CSS
│   │   ├── api_products.php        # Products REST API
│   │   ├── api_orders.php          # Orders REST API
│   │   ├── db_conn.php             # Database connection handler
│   │   ├── index.php               # Admin dashboard & product manager
│   │   ├── orders.php              # Orders management page
│   │   └── .htaccess               # Apache URL rewrite rules & CORS headers
│   ├── Dockerfile                  # PHP 8.2 Apache Docker image
│   ├── docker-compose.yml          # MySQL, phpMyAdmin, and Apache web services
│   ├── database_schema.sql         # SQL table definitions
│   └── seed_products.sql           # Initial 68 product seed dataset
│
├── mobile_app/                     # Flutter Mobile & Web Application
│   ├── lib/                        # Dart source code
│   │   ├── models/                 # Product and Order data models
│   │   ├── screens/                # UI screens (Home, Cart, Orders, Detail)
│   │   └── services/               # ApiService (REST API client)
│   ├── android/                    # Android native project
│   ├── web/                        # Flutter web configuration
│   ├── pubspec.yaml                # Flutter dependencies
│   └── analysis_options.yaml       # Dart analyzer rules
│
├── .gitignore                      # Git ignore rules for build/caches
└── refactor_project.ps1            # Automated folder restructuring script
```

---

## 🚀 Getting Started

### 1. Backend Setup (Docker)

1. Navigate to the backend folder:
   ```bash
   cd backend
   ```
2. Start the Docker containers:
   ```bash
   docker compose up -d
   ```
3. Services and Ports:
   - **Admin Dashboard & API:** [http://localhost:8000](http://localhost:8000)
   - **phpMyAdmin:** [http://localhost:8080](http://localhost:8080)
   - **MySQL Database:** `localhost:3307` (Internal port: `3306`, User: `root`, Password: `password`, DB: `onlysport`)

4. (Optional) Re-seed products manually:
   ```bash
   docker exec -i onlysport_db mysql -u root -ppassword onlysport < seed_products.sql
   ```

---

### 2. Frontend Setup (Flutter Mobile App)

1. Navigate to the Flutter project folder:
   ```bash
   cd mobile_app
   ```
2. Install dependencies:
   ```bash
   flutter pub get
   ```
3. Run the application:
   - **For Chrome / Web:**
     ```bash
     flutter run -d chrome
     ```
   - **For Android Emulator:**
     ```bash
     flutter run -d emulator
     ```

---

## 🌐 Network & API Communication

The mobile application communicates with the backend via `mobile_app/lib/services/api_service.dart`:

- **Flutter Web:** connects to `http://localhost:8000`
- **Android Emulator:** connects to `http://10.0.2.2:8000` (maps to host machine `localhost:8000`)
- **Physical Device:** update `baseUrl` in `api_service.dart` to your computer's local Wi-Fi IP address (e.g. `http://192.168.1.X:8000`).
