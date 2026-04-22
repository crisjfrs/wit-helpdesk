# TicketOne – Helpdesk Ticketing System

## 1. Deskripsi Project

TicketOne adalah sistem helpdesk berbasis web yang digunakan untuk mengelola laporan masalah dari user kepada teknisi.
Sistem ini memungkinkan user membuat tiket laporan, memantau status tiket, serta membantu admin dan teknisi dalam mengelola penyelesaian masalah secara terstruktur.

Fitur utama:

* Manajemen tiket
* Dashboard admin
* Manajemen user
* Manajemen kategori tiket
* Notifikasi tiket
* Laporan tiket

---

## 2. Teknologi yang Digunakan

Project ini dibangun menggunakan teknologi berikut:

* Laravel 12 (PHP Framework)
* PHP 8
* MySQL 8
* Nginx
* Docker
* Docker Compose
* GitLab CI/CD

---

## 3. Arsitektur Sistem

Arsitektur aplikasi menggunakan container Docker dengan struktur berikut:

User
↓
Nginx (Web Server - Docker)
↓
Laravel Application (Docker)
↓
MySQL Database (Docker)

---

## 4. Struktur Container Docker

Aplikasi dijalankan menggunakan beberapa container:

1. **app**

   * Menjalankan aplikasi Laravel

2. **webserver**

   * Menggunakan Nginx sebagai web server

3. **db**

   * Database MySQL untuk menyimpan data aplikasi

---

## 5. Cara Menjalankan Project (Local Development)

### 1. Clone Repository

git clone https://gitlab.com/wit-id/pkl-2026/ticketone.git

### 2. Masuk ke Folder Project

cd ticketone

### 3. Copy File Environment

cp .env.example .env

### 4. Jalankan Docker

docker compose up -d --build

### 5. Install Dependency Laravel

docker compose exec app composer install

### 6. Generate Application Key

docker compose exec app php artisan key:generate

### 7. Migrasi Database

docker compose exec app php artisan migrate

---

## 6. Akses Aplikasi

Setelah container berjalan, aplikasi dapat diakses melalui:

http://localhost:8010

---

## 6.1 Notifikasi Telegram Bot (Tiket Baru)

Sistem sudah mendukung notifikasi Telegram otomatis setiap ada tiket baru.

### 1. Buat Telegram Bot

1. Buka Telegram dan chat ke BotFather
2. Jalankan perintah /newbot
3. Simpan token bot yang diberikan

### 2. Dapatkan Chat ID Tujuan

Gunakan salah satu cara:

* Tambahkan bot ke grup teknisi/admin lalu ambil group chat id
* Gunakan bot seperti @userinfobot untuk chat pribadi

Anda dapat mengisi lebih dari satu chat id dipisahkan koma.

Untuk mode per-user (disarankan), isi chat id tiap admin/teknisi dari menu profil/user management.

### 3. Konfigurasi Environment

Tambahkan pada file .env:

TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=isi_token_bot
TELEGRAM_CHAT_IDS=-1001234567890,123456789
TELEGRAM_TICKET_CREATED_TEMPLATE="Tiket baru masuk\nNomor: {ticket_number}\nJudul: {title}\nPrioritas: {priority}\nKategori: {category}\nPelapor: {reporter}\nLink: {ticket_url}"

Placeholder yang bisa dipakai pada template:

* {ticket_number}
* {title}
* {priority}
* {category}
* {reporter}
* {ticket_url}

Catatan:

* Gunakan \\n pada value env untuk pindah baris.
* Jika admin/teknisi memiliki telegram_chat_id, sistem akan kirim ke chat id per-user.
* TELEGRAM_CHAT_IDS dipakai sebagai fallback jika belum ada chat id per-user.

### 4. Jalankan Queue Worker

Notifikasi Telegram dikirim lewat queue, jadi worker harus aktif:

docker compose exec app php artisan queue:work

Jika worker tidak berjalan, notifikasi tidak akan terkirim realtime.

### 5. Cara Menghubungkan Web App ke Bot Telegram

1. Buat bot via BotFather dan simpan token.
2. Simpan token ke TELEGRAM_BOT_TOKEN.
3. Admin/teknisi wajib memulai chat ke bot minimal sekali (klik Start) agar bot bisa kirim pesan ke user tersebut.
4. Isi Telegram Chat ID di profil masing-masing atau lewat menu edit user.
5. Jalankan queue worker dan buat tiket baru untuk uji notifikasi.

---

## 7. Deployment (GitLab CI/CD)

Project ini menggunakan GitLab CI/CD untuk melakukan deployment otomatis.

Alur deployment:

1. Developer melakukan push code ke branch **development**

git push origin development

2. GitLab CI/CD akan menjalankan pipeline yang terdiri dari:

* Build Docker Image
* Push image ke GitLab Container Registry
* Deploy ke server

3. Server akan menjalankan perintah berikut:

docker compose pull
docker compose up -d

Dengan mekanisme ini, setiap perubahan code akan otomatis terdeploy ke server.

---

## 8. URL Aplikasi Production

Aplikasi dapat diakses melalui:

https://ticketone.wit.co.id

---

## 9. Screenshot Sistem

### Dashboard Admin

Menampilkan statistik tiket, user, dan teknisi yang terdaftar pada sistem.

---

## 10. Tim Pengembang

Project ini dikembangkan sebagai bagian dari kegiatan **Magang**.