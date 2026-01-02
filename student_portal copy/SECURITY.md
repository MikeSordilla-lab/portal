# Security Documentation

This document outlines the security measures implemented in the College Student Portal.

## Authentication Security

### Password Storage

- Passwords are hashed using PHP's `password_hash()` function with the default algorithm (bcrypt)
- Never stored in plain text
- Verified using `password_verify()` for timing-safe comparison

### Session Management

- Sessions are configured with secure settings:
  - `HttpOnly`: Cookies are not accessible via JavaScript
  - `SameSite=Strict`: Prevents CSRF via cookie submission
  - 30-minute session timeout
- Session ID is regenerated on login to prevent session fixation attacks
- Complete session destruction on logout

### Account Lockout

- Accounts are locked after 5 consecutive failed login attempts
- Lockout duration: 30 minutes
- Failed attempts are tracked in the database

## Input Validation

### Server-Side Validation

All user inputs are validated on the server side:

- Email addresses validated with `filter_var()` and regex
- Phone numbers validated for 10-15 digit format
- Dates validated for proper format
- Required fields enforced
- String lengths limited

### Client-Side Validation

Bootstrap's form validation provides immediate user feedback, but all validation is repeated server-side.

## SQL Injection Prevention

- All database queries use PDO prepared statements
- PDO is configured with `ATTR_EMULATE_PREPARES = false` for native prepared statements
- Parameters are bound using named placeholders (`:param`)

Example:

```php
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id' => $id]);
```

## Cross-Site Scripting (XSS) Prevention

- All output is escaped using the `h()` helper function (wrapper for `htmlspecialchars()`)
- Uses `ENT_QUOTES` flag and UTF-8 encoding
- JavaScript content is properly escaped

## Cross-Site Request Forgery (CSRF) Protection

- Every form includes a CSRF token
- Tokens generated using `bin2hex(random_bytes(32))`
- Tokens validated using `hash_equals()` for timing-safe comparison
- Tokens stored in session and regenerated per-request

Implementation:

```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate token
hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
```

## File Upload Security

Profile picture uploads undergo 7-step validation:

1. **File existence check**: Verify file was uploaded
2. **Error check**: Validate no upload errors occurred
3. **Size validation**: Maximum 2MB
4. **MIME type validation**: Only `image/jpeg` and `image/png`
5. **Extension validation**: Only `.jpg`, `.jpeg`, `.png`
6. **Image validation**: Verify file is a valid image using `getimagesize()`
7. **Filename sanitization**: Generate new filename with timestamp

## Directory Security

- Upload directories are outside the web root where possible
- Directory listing is disabled via `.htaccess` (if Apache)
- Only specific file types are allowed

## Error Handling

- Detailed errors are logged using `error_log()`
- Generic error messages are shown to users
- No stack traces or sensitive information exposed
- Production should have `display_errors = Off`

## Recommendations for Production

1. **Use HTTPS**: All traffic should be encrypted
2. **Secure Cookies**: Enable `Secure` flag for cookies
3. **Content Security Policy**: Implement CSP headers
4. **Rate Limiting**: Implement request rate limiting
5. **Database User Privileges**: Use a dedicated database user with minimal privileges
6. **Regular Updates**: Keep PHP, MySQL, and dependencies updated
7. **Audit Logging**: Implement comprehensive audit logging
8. **Backup Strategy**: Regular automated backups with encryption

## Security Headers

For production, add these headers (via Apache or PHP):

```apache
# .htaccess
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "DENY"
Header set X-XSS-Protection "1; mode=block"
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net;"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

## Reporting Vulnerabilities

If you discover a security vulnerability, please report it responsibly to the system administrator. Do not publicly disclose the vulnerability until it has been addressed.

## Compliance Considerations

For educational institutions, consider:

- **FERPA compliance**: Student records are protected
- **Data minimization**: Only collect necessary data
- **Access controls**: Role-based access implemented
- **Audit trails**: Consider implementing for data changes
