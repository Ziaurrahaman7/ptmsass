# Deployment Guide

আপনার প্রজেক্ট deploy করার জন্য ৩টি method আছে। যেকোনো একটি ব্যবহার করতে পারবেন।

## Method 1: PHP Deployment Script (সবচেয়ে সহজ)

### Setup:
1. `deploy.php` file টা edit করুন
2. Line 14-তে `SECRET_TOKEN` change করুন (random string দিন)
3. File টা server এর root directory তে upload করুন

### Deploy করার জন্য:
Browser থেকে এই URL টা hit করুন:
```
https://ptm.boxfair.xyz/deploy.php?token=YOUR_SECRET_TOKEN
```

### সুবিধা:
- SSH access লাগবে না
- Browser থেকেই deploy করা যায়
- Real-time deployment logs দেখা যায়

---

## Method 2: Shell Script (SSH থাকলে)

### Setup:
Server-এ SSH করে এই commands run করুন:
```bash
cd /path/to/your/project
chmod +x deploy.sh
```

### Deploy করার জন্য:
```bash
./deploy.sh
```

---

## Method 3: Artisan Command (সবচেয়ে নিরাপদ)

### Deploy করার জন্য:
Server-এ SSH করে:
```bash
cd /path/to/your/project
php artisan app:deploy
```

Git pull skip করতে চাইলে:
```bash
php artisan app:deploy --skip-git
```

---

## Method 4: GitHub Actions (Automatic Deployment)

### Setup:
1. GitHub repository Settings → Secrets এ যান
2. এই secrets গুলো add করুন:
   - `SSH_HOST`: আপনার server IP/domain
   - `SSH_USERNAME`: SSH username
   - `SSH_PASSWORD`: SSH password (অথবা `SSH_PRIVATE_KEY` use করুন)
   - `PROJECT_PATH`: Project এর full path (যেমন: `/home/username/ptm.boxfair.xyz`)

### Deploy:
main branch-এ push করলেই automatic deploy হবে।

---

## Manual Deployment (Emergency)

যদি কোনো method কাজ না করে, তাহলে SSH করে manually এই commands run করুন:

```bash
cd /path/to/your/project

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Current Issue Fix (403 Error)

আপনার current 403 error fix করার জন্য শুধু এই command টা run করুন:

```bash
cd ptm.boxfair.xyz
php artisan optimize:clear
```

অথবা deploy.php method use করুন (সবচেয়ে সহজ)।

---

## Security Notes:

1. **deploy.php file টা deploy করার পর delete করে দিন** অথবা token টা খুব strong রাখুন
2. `.env` file এ `APP_ENV=production` এবং `APP_DEBUG=false` set করুন
3. Git এ `.env` file কখনো commit করবেন না

---

## Troubleshooting:

### Composer command not found:
```bash
php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

### Permission denied:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Storage link fails:
```bash
rm public/storage
php artisan storage:link
```
