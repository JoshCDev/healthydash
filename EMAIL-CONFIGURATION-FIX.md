# 📧 Cara Memperbaiki Masalah Email Reset Password

## 🔍 Diagnosa Masalah

Masalah "Failed to send reset email" kemungkinan disebabkan oleh:

1. **MAILGUN_API_KEY** belum dikonfigurasi di Vercel
2. **SENDER_EMAIL** belum dikonfigurasi dengan benar
3. API key yang salah atau expired

## 🛠️ Langkah-Langkah Perbaikan

### 1. **Test Konfigurasi Email** (Setelah Login)

Buka URL berikut setelah login untuk melihat status konfigurasi:

```
https://healthydash.vercel.app/test-email-config.php
```

Anda akan melihat informasi seperti:

- `mailgun_api_key_set`: Apakah API key sudah diset
- `sender_email_set`: Apakah sender email sudah diset
- `api_test.success`: Apakah koneksi ke Mailgun berhasil
- `api_test.error`: Error message jika ada

### 2. **Set Environment Variables di Vercel**

1. Login ke dashboard Vercel
2. Pilih project **healthydash**
3. Klik tab **Settings**
4. Klik **Environment Variables** di sidebar
5. Tambahkan environment variables berikut:

```
MAILGUN_API_KEY = [Your Mailgun API Key]
SENDER_EMAIL = noreply@yourdomain.com
```

⚠️ **PENTING**: Pastikan menggunakan Mailgun API key yang valid dari dashboard Mailgun Anda.

### 3. **Mendapatkan Mailgun API Key**

1. Login ke [Mailgun Dashboard](https://app.mailgun.com)
2. Pergi ke **Settings** → **API Keys**
3. Copy **Private API Key** (bukan public key!)
4. Atau buat API key baru jika perlu

### 4. **Verifikasi Domain di Mailgun**

Domain `otp.jflyc.com` harus diverifikasi di Mailgun:

1. Login ke Mailgun dashboard
2. Pergi ke **Sending** → **Domains**
3. Pastikan `otp.jflyc.com` sudah verified
4. Jika belum, ikuti instruksi verifikasi

### 5. **Redeploy Aplikasi**

Setelah menambahkan environment variables:

1. Di dashboard Vercel, klik tab **Deployments**
2. Klik tombol **...** di deployment terbaru
3. Pilih **Redeploy**
4. Tunggu deployment selesai

## 🧪 Testing Setelah Fix

1. Buka `/test-email-config.php` lagi untuk memastikan:

   - `mailgun_api_key_set`: true
   - `api_test.success`: true

2. Test reset password:
   - Logout dari aplikasi
   - Buka halaman login
   - Klik "Forgot Password"
   - Masukkan email/username
   - Email reset password harus terkirim

## ⚠️ Catatan Penting

1. **JANGAN PERNAH** commit API key ke repository
2. Selalu gunakan environment variables untuk sensitive data
3. Hapus `/test-email-config.php` setelah selesai testing untuk keamanan

## 🗑️ Cleanup

Setelah email berfungsi normal, hapus file debug:

```bash
git rm api/test-email-config.php
git commit -m "Remove email debug endpoint"
git push origin main
```

## 📞 Troubleshooting Tambahan

Jika masih bermasalah setelah langkah di atas:

1. **Cek Mailgun Logs**:

   - Login ke Mailgun dashboard
   - Pergi ke **Logs** untuk melihat attempt pengiriman email

2. **Verifikasi API Key Format**:

   - API key harus dimulai dengan `key-` atau format serupa
   - Tidak ada spasi di awal/akhir

3. **Cek Quota Mailgun**:
   - Free tier Mailgun memiliki limit
   - Pastikan belum melebihi quota bulanan

---

💡 **Tips**: Simpan file ini untuk referensi future maintenance!
