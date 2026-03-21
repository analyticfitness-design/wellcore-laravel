# WellCore Launch Readiness Checklist

## Infrastructure
- [ ] Domain DNS configured (wellcorefitness.com)
- [ ] SSL certificate installed and auto-renewing
- [ ] Production server provisioned (min: 2 vCPU, 4GB RAM)
- [ ] PHP 8.4 + required extensions installed
- [ ] MySQL 8.0 configured with backups
- [ ] Redis available (optional but recommended)

## Application
- [ ] APP_ENV=production, APP_DEBUG=false
- [ ] APP_KEY generated
- [ ] APP_URL set to https://wellcorefitness.com
- [ ] All migrations run successfully
- [ ] npm run build completed without errors
- [ ] php artisan config:cache executed
- [ ] php artisan route:cache executed
- [ ] php artisan view:cache executed

## Services
- [ ] Wompi: production keys configured, webhook URL registered
- [ ] Mailjet: SMTP credentials set, domain verified
- [ ] VAPID keys generated for push notifications
- [ ] Anthropic API key set (if AI chatbot enabled)

## SEO
- [ ] sitemap.xml accessible at /sitemap.xml
- [ ] robots.txt configured correctly
- [ ] JSON-LD on all public pages
- [ ] OG meta tags on all pages
- [ ] Google Search Console verified
- [ ] Google Analytics tracking code active

## Content
- [ ] All placeholder images replaced with real photos
- [ ] All dummy text replaced with final copy
- [ ] Legal pages reviewed (privacidad, terminos, cookies, reembolsos)
- [ ] Contact information correct (email, WhatsApp)
- [ ] Social media links verified

## Testing
- [ ] All automated tests passing
- [ ] Manual testing: inscription flow
- [ ] Manual testing: payment flow (Wompi sandbox)
- [ ] Manual testing: login/logout
- [ ] Manual testing: coach dashboard
- [ ] Manual testing: admin dashboard
- [ ] Mobile responsive testing (iPhone, Android)
- [ ] Dark mode verification
- [ ] Cross-browser testing (Chrome, Safari, Firefox)

## Performance
- [ ] Lighthouse score >= 90
- [ ] Page load < 3 seconds
- [ ] Images optimized (WebP where possible)
- [ ] CSS/JS minified (Vite production build)
- [ ] Gzip/Brotli compression enabled on server

## Monitoring
- [ ] Error tracking active (Sentry DSN configured)
- [ ] Uptime monitoring configured
- [ ] Database backup cron active (daily)
- [ ] Log rotation configured
- [ ] Disk space alerts set

## Go-Live
- [ ] Stakeholder sign-off obtained
- [ ] Rollback plan documented
- [ ] Support team briefed
- [ ] Social media announcement ready
- [ ] First 24h monitoring plan in place
