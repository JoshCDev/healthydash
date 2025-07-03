# Address Type Custom Input Fix

## Date: December 2024

## Problem

Users were getting SQL error when trying to add custom address types:

```
SQLSTATE[01000]: Warning: 1265 Data truncated for column 'address_type' at row 1
```

## Root Cause

The `address_type` column in the `user_addresses` table was defined as an ENUM:

```sql
`address_type` enum('Home','Office','Other') NOT NULL DEFAULT 'Home'
```

This meant only exactly 'Home', 'Office', or 'Other' were allowed. When users selected "Other" and typed in custom text (like "School", "Gym", etc.), the database rejected it because it didn't match the ENUM values.

## Solution

Changed the column type from ENUM to VARCHAR(50) to allow custom address types while maintaining data integrity.

### Database Changes

1. **Migration Script**: `database/fix_address_type.sql`

   - Changes `address_type` from ENUM to VARCHAR(50)
   - Adds CHECK constraint for validation
   - Creates backup of existing data

2. **Updated Schema**: `database/healthydash.sql`
   - Updated table definition to use VARCHAR(50)
   - Added CHECK constraint

### Code Changes

1. **Backend Validation**: `api/includes/save_address.php`

   - Updated validation to allow up to 50 characters
   - Added 'Other' to predefined types list

2. **Frontend Validation**: `api/address.php`
   - Updated maxlength from 20 to 50 characters

## Files Modified

- `database/healthydash.sql` - Updated schema
- `database/fix_address_type.sql` - Migration script (new)
- `api/includes/save_address.php` - Backend validation
- `api/address.php` - Frontend validation

## How to Apply the Fix

### For Production Database (Aiven.io)

Run the migration script:

```sql
-- Connect to your Aiven MySQL database
-- Run the contents of database/fix_address_type.sql
```

### For Local Development

1. Run the migration script on your local database
2. Or recreate the database using the updated `healthydash.sql`

## Testing

1. Try adding an address with "Other" type
2. Type in custom text like "School", "Gym", "Friend's House"
3. Should save successfully without SQL errors

## Benefits

- ✅ Users can now add custom address types
- ✅ Maintains data integrity with CHECK constraint
- ✅ Backward compatible with existing data
- ✅ Proper validation on both frontend and backend
