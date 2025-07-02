# 🔧 Perbaikan Mailgun Domain Error

## ❌ **Masalah Teridentifikasi**

Dari output test-email-config.php:

```json
{
  "api_test": {
    "status_code": 404,
    "success": false,
    "error": "Domain not found"
  }
}
```

**Root Cause**: Domain `otp.jflyc.com` tidak terdaftar di Mailgun account Anda.

## 🛠️ **Solusi - Pilih Salah Satu**

### **Opsi A: Gunakan Mailgun Sandbox Domain (Rekomendasi untuk Testing)**

1. **Login ke Mailgun Dashboard**: https://app.mailgun.com
2. **Cari Sandbox Domain**: Biasanya format `sandboxXXXXXX.mailgun.org`
3. **Update Environment Variable di Vercel**:
   ```
   MAILGUN_DOMAIN = sandboxXXXXXX.mailgun.org
   ```
4. **Update SENDER_EMAIL**:
   ```
   SENDER_EMAIL = postmaster@sandboxXXXXXX.mailgun.org
   ```

### **Opsi B: Setup Domain Custom (untuk Production)**

#### **B1. Tambah Domain di Mailgun**

1. Login ke Mailgun Dashboard
2. **Domains** → **Add New Domain**
3. Masukkan domain: `otp.jflyc.com`
4. Pilih region: **US** atau **EU**

#### **B2. Setup DNS Records**

Mailgun akan memberikan DNS records yang harus ditambahkan:

```
TXT Record:
Name: @ atau root domain
Value: v=spf1 include:mailgun.org ~all

CNAME Record:
Name: email.otp.jflyc.com
Value: mailgun.org

MX Records:
Priority: 10, Value: mxa.mailgun.org
Priority: 10, Value: mxb.mailgun.org
```

#### **B3. Verifikasi Domain**

1. Tunggu DNS propagation (5-30 menit)
2. Di Mailgun dashboard, klik **Verify DNS Settings**
3. Status harus menjadi **Verified**

### **Opsi C: Gunakan Domain yang Sudah Ada**

Jika Anda sudah punya domain lain yang ter-setup di Mailgun:

1. **Cek domain yang tersedia**:

   - Login ke Mailgun
   - **Domains** → lihat domain yang sudah verified

2. **Update Environment Variables**:
   ```
   MAILGUN_DOMAIN = your-verified-domain.com
   SENDER_EMAIL = noreply@your-verified-domain.com
   ```

## 🚀 **Langkah Update di Vercel**

1. **Buka Vercel Dashboard**
2. **Project healthydash** → **Settings** → **Environment Variables**
3. **Edit existing variables**:

   - `MAILGUN_DOMAIN` = [domain yang sudah verified]
   - `SENDER_EMAIL` = [email dari domain tersebut]

4. **Redeploy aplikasi**:
   - **Deployments** tab
   - Klik **Redeploy** pada deployment terbaru

## ✅ **Testing Setelah Perbaikan**

1. **Tunggu deployment selesai**
2. **Test konfigurasi**:

   ```
   https://healthydash.vercel.app/test-email-config.php
   ```

   Expected result:

   ```json
   {
     "api_test": {
       "status_code": 200,
       "success": true,
       "error": null
     }
   }
   ```

3. **Test reset password**:
   - Logout dari aplikasi
   - **Forgot Password** → masukkan email
   - Cek inbox untuk email reset

## 📝 **Catatan Penting**

- **Sandbox domain**: Gratis, hanya bisa kirim ke email yang sudah diverifikasi
- **Custom domain**: Bisa kirim ke email manapun, butuh setup DNS
- **Free tier Mailgun**: 5,000 emails per bulan

## 🔄 **Jika Masih Error**

1. **Cek Mailgun logs**: Dashboard → **Logs** untuk melihat attempt pengiriman
2. **Verifikasi API Key**: Pastikan API key dari **Settings** → **API Keys**
3. **Cek quota**: Free tier memiliki limit bulanan

---

💡 **Tip**: Untuk development, gunakan sandbox domain dulu. Untuk production, setup domain custom.
