# Panduan Deployment Production - WIT Helpdesk

## 1. Persiapan Server

### Requirements:
- **OS**: Ubuntu 20.04 LTS atau lebih baru (recommended)
- **RAM**: Minimal 2GB (4GB recommended)
- **Storage**: Minimal 20GB
- **CPU**: 2 core minimal
- **Docker**: Versi 20.10+
- **Docker Compose**: Versi 1.29+

### Install Docker & Docker Compose:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify
docker --version
docker-compose --version
```

---

## 2. Setup Project di Server

```bash
# Clone repository
git clone https://gitlab.com/wit-id/pkl-2026/ticketone.git /var/www/helpdesk
cd /var/www/helpdesk

# Checkout production branch (jika ada)
git checkout production

# Set permissions
sudo chown -R $USER:$USER /var/www/helpdesk
chmod -R 755 /var/www/helpdesk
```

---

## 3. Konfigurasi Environment Production

### Buat `.env` dari `.env.example`:

```bash
cp .env.example .env
nano .env
```

### Edit nilai-nilai di `.env` untuk production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ticketone.wit.co.id

# Database (gunakan managed DB atau external server, bukan container)
DB_CONNECTION=mysql
DB_HOST=db.example.com
DB_PORT=3306
DB_DATABASE=helpdesk_production
DB_USERNAME=helpdesk_user
DB_PASSWORD=GANTI_PASSWORD_YANG_KUAT

# Cache & Session - gunakan Redis untuk performance
CACHE_STORE=redis
SESSION_DRIVER=cookie

# Queue - bisa tetap database, tapi Redis lebih cepat
QUEUE_CONNECTION=redis

# Redis connection
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=helpdesk@company.com
MAIL_PASSWORD=APP_PASSWORD_GMAIL
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=helpdesk@company.com
MAIL_FROM_NAME="WIT Helpdesk"

# Telegram Bot
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=TOKEN_BOT_PRODUCTION
TELEGRAM_CHAT_IDS=CHAT_ID_GROUP_ADMIN
TELEGRAM_TICKET_CREATED_TEMPLATE="Tiket baru masuk\nNomor: {ticket_number}\nJudul: {title}\nPrioritas: {priority}\nKategori: {category}\nPelapor: {reporter}\nLink: {ticket_url}"
```

**Penting**: Ganti semua credential dengan nilai production yang aman!

---

## 4. Update Docker Compose untuk Production

Buat file `docker-compose.prod.yml`:

```yaml
version: "3.8"

services:
  app:
    build: .
    container_name: helpdesk_app
    restart: always
    working_dir: /var/www
    environment:
      - APP_ENV=production
    volumes:
      - ./:/var/www
    ports:
      - "9000:9000"
    networks:
      - helpdesk_network

  webserver:
    image: nginx:alpine
    container_name: helpdesk_nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt  # SSL certificates
    networks:
      - helpdesk_network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: helpdesk_db
    restart: always
    environment:
      MYSQL_DATABASE: helpdesk_production
      MYSQL_ROOT_PASSWORD: ROOT_PASSWORD
      MYSQL_USER: helpdesk_user
      MYSQL_PASSWORD: DB_PASSWORD
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - helpdesk_network

  redis:
    image: redis:7-alpine
    container_name: helpdesk_redis
    restart: always
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - helpdesk_network

volumes:
  db_data:
  redis_data:

networks:
  helpdesk_network:
    driver: bridge
```

---

## 5. Setup SSL/HTTPS dengan Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Generate certificate
sudo certbot certonly --standalone -d ticketone.wit.co.id

# Certificate auto-renew
sudo certbot renew --dry-run
```

Update `docker/nginx/default.conf`:

```nginx
server {
    listen 443 ssl http2;
    server_name ticketone.wit.co.id;

    ssl_certificate /etc/letsencrypt/live/ticketone.wit.co.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ticketone.wit.co.id/privkey.pem;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/www/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Redirect HTTP ke HTTPS
server {
    listen 80;
    server_name helpdesk.wit.co.id;
    return 301 https://$server_name$request_uri;
}
```

---

## 6. Build & Run Container

```bash
# Build image
docker-compose -f docker-compose.prod.yml build

# Run container
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Seed database (optional)
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed

# Generate app key
docker-compose -f docker-compose.prod.yml exec app php artisan key:generate

# Clear cache
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:clear

# Create storage link
docker-compose -f docker-compose.prod.yml exec app php artisan storage:link
```

---

## 7. Setup Queue Worker

Queue worker harus selalu berjalan agar notifikasi Telegram terkirim.

### Option A: Supervisor (Recommended)

```bash
# Install Supervisor
sudo apt install supervisor -y

# Buat file konfigurasi
sudo nano /etc/supervisor/conf.d/helpdesk-worker.conf
```

Isi file:

```ini
[program:helpdesk-worker]
process_name=%(program_name)s_%(process_num)02d
command=docker-compose -f /var/www/helpdesk/docker-compose.prod.yml exec app php artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/helpdesk/storage/logs/worker.log
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start helpdesk-worker:*
```

### Option B: Cron Job (Minimal)

```bash
# Edit crontab
crontab -e

# Tambahkan:
* * * * * cd /var/www/helpdesk && docker-compose -f docker-compose.prod.yml exec -T app php artisan queue:work --once
```

---

## 8. Setup Backups

```bash
# Buat script backup
sudo nano /usr/local/bin/backup-helpdesk.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/backups/helpdesk"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup database
docker-compose -f /var/www/helpdesk/docker-compose.prod.yml exec db mysqldump -u helpdesk_user -p$DB_PASSWORD helpdesk_production > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/helpdesk/storage/app

# Hapus backup lama (lebih dari 30 hari)
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Set permissions dan cron:

```bash
sudo chmod +x /usr/local/bin/backup-helpdesk.sh

# Cron daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-helpdesk.sh
```

---

## 9. Monitoring & Logging

### Check container status:

```bash
docker-compose -f docker-compose.prod.yml ps
```

### View logs:

```bash
# App logs
docker-compose -f docker-compose.prod.yml logs -f app

# Nginx logs
docker-compose -f docker-compose.prod.yml logs -f webserver

# Queue worker logs
tail -f /var/www/helpdesk/storage/logs/laravel.log
```

### Setup log rotation:

```bash
sudo nano /etc/logrotate.d/helpdesk
```

```
/var/www/helpdesk/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
}
```

---

## 10. CI/CD dengan GitLab

Update `.gitlab-ci.yml`:

```yaml
stages:
  - test
  - build
  - deploy

variables:
  DOCKER_IMAGE: $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA

test:
  stage: test
  script:
    - docker build -t $DOCKER_IMAGE .
    - docker run --rm $DOCKER_IMAGE php artisan test

build:
  stage: build
  script:
    - docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD $CI_REGISTRY
    - docker build -t $DOCKER_IMAGE .
    - docker push $DOCKER_IMAGE

deploy_production:
  stage: deploy
  script:
    - ssh -i $SSH_KEY $DEPLOY_USER@$DEPLOY_HOST "cd /var/www/helpdesk && git pull origin production && docker-compose -f docker-compose.prod.yml pull && docker-compose -f docker-compose.prod.yml up -d && docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force"
  only:
    - production
  environment:
    name: production
    url: https://ticketone.wit.co.id
```

---

## 11. Security Checklist

- [ ] Ganti semua default passwords
- [ ] Set `APP_DEBUG=false`
- [ ] Enable SSL/HTTPS
- [ ] Update `.env` dengan credential production
- [ ] Setup firewall rules
- [ ] Regular security updates: `sudo apt update && apt upgrade`
- [ ] Monitor failed login attempts
- [ ] Setup email alerts untuk errors
- [ ] Regular backups tersimpan aman
- [ ] Hide sensitive files di `.gitignore`

---

## 12. Troubleshooting

### Aplikasi tidak muncul:

```bash
docker-compose -f docker-compose.prod.yml logs app
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
```

### Database connection error:

```bash
docker-compose -f docker-compose.prod.yml exec db mysql -u helpdesk_user -p$DB_PASSWORD -e "SELECT 1"
```

### Queue worker tidak jalan:

```bash
sudo supervisorctl status helpdesk-worker:*
sudo supervisorctl restart helpdesk-worker:*
```

### Telegram notifikasi tidak terkirim:

```bash
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
>>> \App\Models\User::where('role', 'admin')->first()->telegram_chat_id
```

---

## Kontak Support

Untuk pertanyaan deployment, hubungi tim infrastructure.
