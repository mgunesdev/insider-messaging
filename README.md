<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Insider Messaging Project

Laravel 10 tabanlı mesaj gönderim sistemi.  
Özellikler:
- Repository + Service katmanı
- Queue/Job yapısı (Redis)
- Global rate-limit (5 saniyede 2 mesaj)
- Gönderilen mesajların DB’de işaretlenmesi
- REST API: Gönderilen mesajların `messageId` listesi
- Swagger/OpenAPI dokümantasyonu
- Docker Compose ortamı (Laravel, PostgreSQL, Redis)
- **entrypoint.sh ile otomatik ilk kurulum**

---

## 🚀 Kurulum

### 1. Projeyi klonla
```bash
git clone <repo-url> insider-messaging
cd insider-messaging
```

### 2. Docker ortamını başlat
```bash
docker compose up -d --build
```

Servisler:
- Laravel API: [http://localhost:8000](http://localhost:8000)  
- PostgreSQL: `localhost:5432` (user: postgres / pass: postgres)  
- Redis: `localhost:6379`

> `entrypoint.sh` otomatik olarak `.env` oluşturur, `php artisan key:generate` ve `php artisan migrate` çalıştırır.

---

## 📨 Mesaj Gönderim Süreci

1. `messages` tablosuna `pending` kayıtlar eklenir.  
2. Kuyruklama komutu ile job’lar hazırlanır:
   ```bash
   docker exec -it insider_app php artisan messages:dispatch-pending
   ```
3. Worker başlatılır:
   ```bash
   docker exec -it insider_app php artisan queue:work --queue=messages --sleep=1
   ```
4. Rate limit: **her 5 saniyede max 2 mesaj** gönderilir.  

---

## 📡 API Kullanımı

### Gönderilen mesaj listesi
```http
GET /api/messages/sent
```

Örnek cevap:
```json
{
  "data": [
    {
      "id": 1,
      "to": "+905555555555",
      "messageId": "abc-123",
      "sent_at": "2025-09-27T11:20:31Z"
    }
  ]
}
```

Swagger dokümanı:  
[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

---

## 🧪 Testler

```bash
docker exec -it insider_app php artisan test
```

---

## 📦 Faydalı Komutlar

```bash
# Container içine girmek
docker exec -it insider_app bash

# Migration geri alıp tekrar çalıştırmak
docker exec -it insider_app php artisan migrate:fresh --seed

# Kuyruktaki job sayısını görmek
docker exec -it insider_app php artisan queue:failed
```

---

## ⚙️ Notlar
- Mesaj içerik uzunluğu `.env` üzerinden ayarlanır: `MSG_MAX_LENGTH=160`  
- Gönderilen mesajlar Redis’e de cachelenir (`messages:sent:{id}`)  
- Queue gözlemi için Horizon kurulabilir:
  ```bash
  composer require laravel/horizon
  php artisan horizon
  ```
