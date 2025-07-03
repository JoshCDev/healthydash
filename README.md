# HealthyDash 🥗

## Deskripsi Proyek

HealthyDash adalah platform pemesanan makanan sehat berbasis web yang dirancang untuk memudahkan pengguna dalam memesan makanan bergizi dengan antarmuka yang modern dan user-friendly. Aplikasi ini menyediakan sistem pemesanan lengkap dengan autentikasi pengguna, manajemen alamat, dan nilai gizi serta cara pembuatan suatu makanan.

## 🚀 Teknologi yang Digunakan

### Backend

- **PHP 8.x** - Server-side scripting
- **MySQL** - Database relational untuk penyimpanan data
- **Aiven.io** - Cloud database hosting
- **PDO** - PHP Data Objects untuk koneksi database yang aman

### Frontend

- **HTML5 & CSS3** - Struktur dan styling modern
- **JavaScript (Vanilla)** - Interaktivitas dan validasi real-time
- **Responsive Design** - Kompatibel dengan semua perangkat

### Autentikasi & Keamanan

- **Google OAuth 2.0** - Sign-in dengan akun Google
- **Custom Authentication** - Sistem login/register tradisional
- **OTP Verification** - Verifikasi email dengan kode OTP
- **Password Hashing** - Pemrosesan password dengan algoritma SHA-256 yang bersifat satu arah sehingga privasi pengguna terjaga dan tidak bisa dibaca oleh developer

### Infrastruktur & Deployment

- **Vercel** - Platform hosting serverless
- **Mailgun** - Service pengiriman email
- **Composer** - Dependency management untuk PHP
- **Git** - Version control system

### Development Tools

- **Session Management** - Custom session handler untuk environment serverless
- **Error Logging** - Comprehensive error tracking
- **Database Migration** - Sistem migrasi database yang aman

## ✨ Fitur Utama

### 🔐 Sistem Autentikasi

- **Registrasi Pengguna**

  - Validasi form real-time
  - Verifikasi email dengan OTP
  - Password strength indicator
  - Duplikasi check untuk username/email

- **Login Fleksibel**
  - Login dengan email atau username
  - Integrasi Google Sign-In
  - Remember me functionality
  - Password reset via username/email

### 🍽️ Sistem Pemesanan

- **Menu Interaktif**

  - Katalog makanan sehat dengan gambar
  - Deskripsi detail nutrisi
  - Sistem kategori makanan
  - Harga yang jelas dan transparan

- **Keranjang Belanja**
  - Add/remove items dengan mudah
  - Kalkulasi total otomatis
  - Simpan keranjang untuk session

### 📍 Manajemen Alamat

- **Multiple Address Support**
  - Simpan multiple alamat pengiriman
  - Custom address types (Rumah, Kantor, atau custom)
  - Integrasi Google Maps API
  - Set alamat default

### 👤 Profil Pengguna

- **Account Management**
  - Edit profil pengguna
  - Ganti password
  - Riwayat pesanan
  - Pengaturan notifikasi

### 🔒 Keamanan & Privasi

- **Data Protection**

  - Enkripsi data sensitif
  - Secure session management
  - SQL injection protection
  - XSS prevention

- **Privacy Compliance**
  - Terms of Service
  - Privacy Policy
  - GDPR-ready data handling

## 🛠️ Petunjuk Setup

### Prasyarat

```bash
- PHP 8.0 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Composer
- Web server (Apache/Nginx) atau PHP built-in server
- Akun Google Developer Console
- Akun Mailgun
- Akun Aiven.io (untuk production database)
```

### 1. Clone Repository

```bash
git clone https://github.com/JoshCDev/healthydash.git
cd healthydash
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Database

#### Development (Local)

```sql
-- Buat database local
CREATE DATABASE healthydash;

-- Import schema
mysql -u username -p healthydash < database/healthydash.sql
```

#### Production (Cloud Database)

```bash
# Database dikonfigurasi di cloud provider (Aiven.io)
# Credentials disimpan sebagai environment variables di Vercel
# Tidak hardcode credentials di code repository
```

### 4. Konfigurasi Environment Variables

**⚠️ PENTING: Keamanan Credentials**

- **JANGAN PERNAH** commit file `.env` ke repository
- **JANGAN** hardcode API keys atau password di dalam code
- Gunakan environment variables untuk semua kredensial sensitif
- File `environment-template.txt` hanya berisi template tanpa nilai asli

Buat file `.env` berdasarkan `environment-template.txt`:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=healthydash
DB_USER=root
DB_PASS=your_password
DB_PORT=3306

# Production Database (Aiven.io) - Set in Vercel Environment
# DB_HOST=your_production_host
# DB_PORT=your_production_port
# DB_NAME=your_production_database
# DB_USER=your_production_user
# DB_PASS=your_production_password

# Application Settings
SITE_URL=http://localhost:8080
VERCEL=0

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

# Email Configuration
MAILGUN_API_KEY=your_mailgun_api_key
MAILGUN_DOMAIN=your_mailgun_domain
SENDER_EMAIL=noreply@yourdomain.com
```

### 5. Setup Google OAuth

1. Buka [Google Developer Console](https://console.developers.google.com/)
2. Buat project baru atau pilih existing project
3. Enable Google+ API
4. Buat OAuth 2.0 credentials
5. Tambahkan authorized origins:
   - `http://localhost:8080` (development)
   - `https://yourdomain.vercel.app` (production)

### 6. Setup Mailgun

1. Daftar di [Mailgun](https://www.mailgun.com/)
2. Verify domain Anda
3. Dapatkan API key dari dashboard
4. Konfigurasikan DNS records sesuai petunjuk Mailgun

### 7. Jalankan Aplikasi

#### Development

```bash
# Gunakan PHP built-in server
php -S localhost:8080

# Atau gunakan XAMPP/WAMP
# Akses via http://localhost/healthydash
```

#### Production (Vercel)

```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
vercel

# Set environment variables di Vercel dashboard
# PENTING: Semua credentials sensitif harus diset di Vercel Environment Variables
# Tidak boleh ada hardcoded values di dalam code atau repository
```

### 8. Konfigurasi Additional

#### Database Migration untuk Address Types

```bash
# Jalankan script migrasi untuk mengubah address_type ke VARCHAR
php run-migration-script.php
```

#### Setup Session Storage

```bash
# Untuk production di Vercel, session disimpan di database
# Tabel sessions akan dibuat otomatis
```

## 🔐 Keamanan Credentials

### Best Practices untuk Environment Variables

1. **Development (Local)**

   ```bash
   # Buat file .env di root directory (sudah ada di .gitignore)
   # Copy dari environment-template.txt
   # Isi dengan values development Anda
   ```

2. **Production (Vercel)**

   ```bash
   # Jangan commit credentials ke repository
   # Set semua environment variables di Vercel Dashboard:
   # Settings > Environment Variables
   ```

3. **File yang HARUS diabaikan Git:**
   ```bash
   .env
   .env.local
   .env.production
   config-production.php (jika berisi credentials)
   ```

### Credentials yang Perlu Diproteksi

- Database passwords
- API keys (Mailgun, Google Maps, dll)
- OAuth client secrets
- Session secrets
- Encryption keys

### Verifikasi Keamanan

```bash
# Pastikan tidak ada credentials di repository
git log --all --full-history -- "*.env*"
grep -r "password\|api_key\|secret" --exclude-dir=.git --exclude="*.md"
```

## 🤖 Dukungan AI dalam Pengembangan

### Penggunaan AI Assistant (Claude/Cursor)

Proyek HealthyDash dikembangkan dengan bantuan AI assistant untuk meningkatkan kualitas code dan mempercepat development process:

#### 🔧 Code Generation & Optimization

- **Automated Code Writing**: AI membantu generate boilerplate code untuk CRUD operations
- **Code Review**: Analisis otomatis untuk mendeteksi potential bugs dan security issues
- **Refactoring**: Optimasi struktur code untuk better maintainability
- **Documentation**: Auto-generate comments dan dokumentasi teknis

#### 🐛 Debugging & Problem Solving

- **Error Analysis**: AI menganalisis error logs dan memberikan solusi
- **Performance Optimization**: Identifikasi bottlenecks dan suggest improvements
- **Security Audit**: Scan untuk common vulnerabilities (SQL injection, XSS, dll)
- **Database Optimization**: Optimize queries dan database schema

#### 🎨 UI/UX Enhancement

- **Responsive Design**: AI membantu create mobile-friendly layouts
- **Accessibility**: Ensure WCAG compliance untuk better accessibility
- **User Experience**: Analyze user flow dan suggest improvements
- **Cross-browser Compatibility**: Test dan fix compatibility issues

#### 📚 Learning & Best Practices

- **Code Standards**: Enforce PSR standards untuk PHP
- **Modern Practices**: Implement latest web development trends
- **Architecture Patterns**: Apply MVC dan other design patterns
- **Testing Strategies**: Suggest test cases dan validation scenarios

### Workflow dengan AI

1. **Planning Phase**

   - AI membantu breakdown requirements menjadi tasks
   - Generate user stories dan acceptance criteria
   - Create project timeline dan milestones

2. **Development Phase**

   - Real-time code suggestions
   - Auto-completion untuk complex functions
   - Pattern recognition untuk consistent coding style
   - Integration suggestions untuk third-party services

3. **Testing Phase**

   - Generate test scenarios
   - Automated bug detection
   - Performance profiling
   - Security vulnerability scanning

4. **Deployment Phase**
   - Environment configuration validation
   - Deployment script optimization
   - Monitoring setup recommendations
   - Rollback strategy planning

### Benefits of AI-Assisted Development

#### ⚡ Kecepatan Development

- **80% faster coding** dengan AI auto-completion
- **Instant debugging** dengan error analysis
- **Rapid prototyping** untuk new features
- **Automated documentation** generation

#### 🎯 Kualitas Code

- **Consistent code style** across the project
- **Best practices enforcement** automatically
- **Security vulnerabilities** detected early
- **Performance optimizations** suggested proactively

#### 📈 Learning & Growth

- **Real-time learning** dari AI suggestions
- **Modern techniques** implementation
- **Industry standards** compliance
- **Continuous improvement** dalam coding skills

### AI Tools Yang Digunakan

1. **Cursor IDE**

   - Code completion dan generation
   - Real-time error detection
   - Refactoring suggestions
   - Documentation generation

2. **Claude Assistant**

   - Complex problem solving
   - Architecture planning
   - Code review dan optimization
   - Best practices implementation

3. **GitHub Copilot** (Optional)
   - Code suggestions
   - Pattern completion
   - Function generation
   - Comment-to-code conversion

## 📝 Dokumentasi Tambahan

### File Dokumentasi dalam Project

- `ADDRESS-TYPE-FIX.md` - Fix untuk custom address types
- `MIGRATION-COMPLETED.md` - Status migrasi database
- `SESSION-LOGOUT-FIXES.md` - Perbaikan sistem session
- `LOGIN-OTP-FIXES.md` - Fix untuk autentikasi OTP
- `VALIDATION-UPDATE.md` - Update sistem validasi form
- `DEPLOYMENT.md` - Panduan deployment lengkap
- `VERCEL-SESSION-FIXES.md` - Fix session untuk Vercel deployment

### API Endpoints

- `POST /api/login.php` - User authentication
- `POST /api/signup.php` - User registration
- `POST /api/check-availability.php` - Check username/email availability
- `GET /api/menu.php` - Get menu items
- `POST /api/place-order.php` - Place new order
- `GET /api/order-history.php` - Get user order history
- `POST /api/save-address.php` - Save user address

### Database Schema

Lihat file `database/healthydash.sql` untuk struktur database lengkap.

## 🤝 Kontribusi

1. Fork repository ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Project ini menggunakan MIT License. Lihat file `LICENSE` untuk detail lengkap.

## 👥 Tim Pengembang

- **Lead Developer**: [Josh C](https://github.com/JoshCDev)
- **AI Assistant**: Claude (Anthropic)
- **Development Environment**: Cursor IDE

## 📞 Kontak & Support

- **Email**: support@healthydash.com
- **GitHub Issues**: [Create Issue](https://github.com/JoshCDev/healthydash/issues)
- **Documentation**: [Wiki](https://github.com/JoshCDev/healthydash/wiki)

---

**HealthyDash** - _Makan Sehat, Hidup Sehat_ 🌱

Made with ❤️ and AI assistance
