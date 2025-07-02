# HealthyDash - Vercel Deployment Guide

Este guide akan membantu Anda mendeploy aplikasi HealthyDash ke Vercel menggunakan PHP runtime dan MySQL database dari Aiven.io.

## Prerequisites

1. **Vercel Account** - [Sign up di Vercel](https://vercel.com/signup)
2. **Aiven.io Account** - [Sign up di Aiven.io](https://aiven.io/signup) untuk MySQL database
3. **Git Repository** - Push kode ini ke GitHub/GitLab/Bitbucket

## Database Setup (Aiven.io)

### 1. Create MySQL Database di Aiven.io

1. Login ke [Aiven Console](https://console.aiven.io/)
2. Klik "Create service"
3. Pilih "MySQL"
4. Pilih cloud provider dan region (rekomendasi: sama dengan Vercel deployment region)
5. Pilih plan yang sesuai (Basic untuk testing)
6. Tunggu sampai service ready (biasanya 5-10 menit)

### 2. Setup Database Schema

1. Download connection info dari Aiven console
2. Connect ke database menggunakan MySQL client atau phpMyAdmin
3. Import schema dari file `database/healthydash.sql`

```sql
-- Atau jalankan SQL ini langsung:
SOURCE healthydash.sql;
```

### 3. Get Connection Details

Dari Aiven console, catat:

- **Host**: `your-service-name.aivencloud.com`
- **Port**: `3306`
- **Username**: `avnadmin`
- **Password**: (generated password)
- **Database Name**: `defaultdb` (atau buat database baru bernama `healthydash`)

## Vercel Deployment

### 1. Connect Repository

1. Login ke [Vercel Dashboard](https://vercel.com/dashboard)
2. Klik "New Project"
3. Import repository yang sudah berisi kode ini
4. Vercel akan auto-detect sebagai static site (kita akan override ini)

### 2. Configure Build Settings

Di project settings, set:

- **Framework Preset**: Other
- **Build Command**: (kosongkan)
- **Output Directory**: (kosongkan)
- **Install Command**: `composer install`

### 3. Set Environment Variables

Di Vercel project settings → Environment Variables, tambahkan:

```
DB_HOST=your-aiven-host.aivencloud.com
DB_NAME=healthydash
DB_USER=avnadmin
DB_PASS=your-aiven-password
DB_PORT=3306
DB_SSL_MODE=REQUIRED

SITE_URL=https://your-app.vercel.app

GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_MAPS_API_KEY=your-google-maps-api-key
MAILGUN_API_KEY=your-mailgun-api-key
MAILGUN_DOMAIN=your-domain.com
SENDER_EMAIL=verification@your-domain.com

VERCEL=1
```

### 4. Deploy

1. Klik "Deploy" di Vercel
2. Tunggu sampai deployment selesai
3. Test aplikasi di URL yang diberikan

## File Structure

Struktur file sudah disesuaikan untuk Vercel:

```
/
├── api/                    # PHP functions
│   ├── index.php          # Main router
│   ├── signup.php         # Signup page
│   ├── login.php          # Login page
│   ├── menu.php           # Menu page
│   ├── cart.php           # Cart page
│   └── includes/          # PHP utilities
│       ├── config.php     # Database config
│       ├── auth_check.php # Authentication
│       └── ...
├── assets/                # Static assets
│   ├── images/
│   └── font/
├── vendor/                # Composer dependencies
├── vercel.json           # Vercel configuration
├── composer.json         # PHP dependencies
└── .vercelignore         # Ignored files
```

## Troubleshooting

### Database Connection Issues

1. **SSL Certificate Error**:

   - Pastikan `DB_SSL_MODE=REQUIRED` di environment variables
   - Aiven.io menggunakan SSL secara default

2. **Connection Timeout**:

   - Pastikan Vercel region dekat dengan Aiven database region
   - Check firewall settings di Aiven

3. **Authentication Failed**:
   - Verify username/password dari Aiven console
   - Pastikan user memiliki access ke database

### Application Issues

1. **404 Errors**:

   - Check `vercel.json` routing configuration
   - Pastikan semua file ada di direktori `api/`

2. **PHP Errors**:

   - Check Vercel function logs
   - Pastikan semua dependencies di `composer.json`

3. **Session Issues**:
   - Serverless functions stateless, session disimpan di cookie
   - Check security settings untuk HTTPS

## Performance Tips

1. **Database Connection Pooling**:

   - Disable persistent connections (sudah di config)
   - Use connection timeout settings

2. **Asset Optimization**:

   - Compress images di folder `assets/`
   - Use CDN untuk static assets jika perlu

3. **Caching**:
   - Enable browser caching untuk assets
   - Consider Redis cache untuk session jika perlu scale

## Security Considerations

1. **Environment Variables**:

   - Never commit secrets ke git
   - Use Vercel environment variables

2. **Database Security**:

   - Aiven.io sudah include SSL dan firewall
   - Use strong passwords

3. **Application Security**:
   - HTTPS enforced di production
   - Secure cookie settings
   - Input validation sudah implemented

## Domain Setup (Optional)

1. Beli domain atau gunakan subdomain
2. Di Vercel project settings → Domains
3. Add custom domain
4. Update `SITE_URL` environment variable
5. Update Google OAuth authorized domains

## Monitoring

1. **Vercel Analytics**: Enable di project settings
2. **Error Tracking**: Check function logs di Vercel dashboard
3. **Database Monitoring**: Aiven console menyediakan metrics
4. **Uptime Monitoring**: Setup external monitoring tools

## Support

Jika ada masalah:

1. Check Vercel function logs
2. Check Aiven database logs
3. Verify environment variables
4. Test database connection manual

---

**Deployment Checklist:**

- [ ] Database created and schema imported
- [ ] Environment variables configured
- [ ] Repository connected to Vercel
- [ ] First deployment successful
- [ ] Database connection working
- [ ] Authentication flow working
- [ ] Asset files loading correctly
- [ ] Domain configured (if using custom domain)
