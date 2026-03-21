# WellCore Security Checklist

## Pre-Launch

### Authentication
- [ ] WellCoreGuard validates tokens correctly
- [ ] Token expiry enforced (30 days)
- [ ] Password hashing uses bcrypt
- [ ] Rate limiting on login (5/min)
- [ ] CSRF protection on all forms
- [ ] Session fixation prevention

### Authorization
- [ ] Admin routes protected by role middleware
- [ ] Coach routes restricted to active coaches
- [ ] Client routes restricted to authenticated clients
- [ ] API endpoints require authentication

### Data Protection
- [ ] SQL injection prevention (Eloquent parameterized)
- [ ] XSS prevention (Blade auto-escaping)
- [ ] CSRF tokens on all forms
- [ ] File upload validation (type + size)
- [ ] Input sanitization on user content

### Headers & Transport
- [ ] CSP headers configured
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: SAMEORIGIN
- [ ] HTTPS enforced in production
- [ ] HSTS header set
- [ ] Secure cookies (Secure + HttpOnly + SameSite)

### Infrastructure
- [ ] .env not committed to git
- [ ] Debug mode OFF in production
- [ ] Error details hidden from users
- [ ] Log files not publicly accessible
- [ ] Backup encryption enabled
- [ ] Database credentials rotated

### Payments
- [ ] Wompi webhook signature verification
- [ ] Payment amounts validated server-side
- [ ] No sensitive payment data stored
- [ ] PCI DSS compliance for card handling

### GDPR / Ley 1581
- [ ] Privacy policy accessible
- [ ] Cookie consent implemented
- [ ] Data export available (DataExportService)
- [ ] Data deletion available (anonymization)
- [ ] Audit logging active

## Post-Launch Monitoring
- [ ] Error tracking configured (Sentry)
- [ ] Uptime monitoring active
- [ ] SSL certificate auto-renewal
- [ ] Database backup automation verified
- [ ] Rate limiting tested under load
