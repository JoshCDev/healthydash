# Vercel Session and Email Fixes

## Date: December 2024

### Issues Fixed:

## 1. Email Images Not Showing

**Problem**: Email templates were using relative paths `../assets/images/healthydashlogo.png` which don't work in emails.

**Solution**:

- Updated both `otp_handler.php` and `PasswordReset.php` to use full URLs
- Now using `SITE_URL` constant to build absolute URLs: `$site_url . '/assets/images/healthydashlogo.png'`

**Files Modified**:

- `api/includes/otp_handler.php` (line 116)
- `api/includes/PasswordReset.php` (line 241)

## 2. Reset Password Page Errors

**Problem**:

- Session already started error
- Include path error for config.php

**Solution**:

- Removed `session_start()` from `reset-password.php` (handled by index.php)
- Changed include path from `../includes/config.php` to `__DIR__ . '/includes/config.php'`

**Files Modified**:

- `api/reset-password.php` (lines 1-3)

## 3. Sudden Logout Issues (Serverless Session Problem)

**Problem**: Vercel's serverless environment doesn't persist PHP sessions between function invocations, causing users to be logged out randomly.

**Solution**: Implemented custom database session handler

- Created `DatabaseSessionHandler` class that stores sessions in MySQL
- Sessions table created automatically with proper indexes
- Session data persists across serverless function calls
- Automatic cleanup of expired sessions

**New Features**:

- Database-backed sessions for reliability
- Session lifetime of 24 hours
- Automatic session garbage collection
- User session cleanup on logout

**Files Created/Modified**:

- `api/includes/session_handler.php` (new file)
- `api/index.php` (added session handler initialization)
- `api/includes/config.php` (added VERCEL constant, removed duplicate session_start)

## 4. Session Configuration Updates

**Configuration**:

```php
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 86400); // 24 hours
```

## Database Schema Addition

New `sessions` table:

```sql
CREATE TABLE sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT(11) DEFAULT NULL,
    data TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
)
```

## Testing Instructions:

1. Deploy to Vercel
2. Test login - should persist across page refreshes
3. Test password reset flow - email should show logo properly
4. Monitor for any logout issues - should be resolved

## Environment Variables Required:

- VERCEL=1 (automatically set by Vercel)
- SITE_URL (e.g., https://yourdomain.vercel.app)
- All other existing env vars (DB_PASS, MAILGUN_API_KEY, etc.)

## Notes:

- Sessions now stored in database for persistence
- All email images use absolute URLs
- Session cookies configured for security
- Automatic cleanup of old sessions
