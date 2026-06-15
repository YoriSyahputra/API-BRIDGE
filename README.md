<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-333333?style=for-the-badge&logo=php&logoColor=white&labelColor=777BB4" alt="PHP Version">

  <img src="https://img.shields.io/badge/Laravel-Framework-333333?style=for-the-badge&logo=laravel&logoColor=white&labelColor=FF2D20" alt="Laravel Version">
  
  <img src="https://img.shields.io/badge/Octane-Powered-333333?style=for-the-badge&logo=linux&logoColor=white&labelColor=000000" alt="Octane Powered">
</p>


<h1 align="center">Food Delivery Middleware API</h1>

This middleware API acts as a secure bridge between the food delivery client application and the .NET Map Server. It accurately handles distance calculations and shipping fee computations while serving as a robust security layer right before hitting the external server.

---

## ✨ Key Features

- **Routing & Proxy**: Securely forwards origin and destination coordinate requests to the Map Server.
- **Dynamic Pricing Engine**: Automatically computes shipping fees based on the exact distance (in KM) returned by the external Map Server.
- **Security & Anti-Spam**: Implements an `X-Idempotency-Key` utilizing the Cache system (60-second TTL). This prevents duplicate transactions and handles user request spamming effectively.
- **JWT Authentication**: Dynamically injects a JSON Web Token into the HTTP Client's request header to authenticate with the central server.
- **Role-Based Data Gate**: Separates the JSON response output. Admin requests receive the full data array, while User requests receive a simplified, formatted payload using the `PrivateKeyScheme`.

## 🛠 Tech Stack & System Requirements

- **Framework:** Laravel
- **Language:** PHP 8.2+
- **Server Engine:** Laravel Octane (supported by FrankenPHP/Swoole/RoadRunner)
- **Cache Driver:** File / Redis
- **HTTP Client:** Laravel HTTP Facade

## 🚀 Installation & Setup

1. Clone this repository.
2. Install all dependencies using Composer:
```bash
   composer install

```
 3. Copy the environment configuration file:
```bash
   cp .env.example .env

```
 4. Generate the application key:
```bash
   php artisan key:generate

```
## ⚙️ Environment Configuration (.env)
This API relies heavily on the Cache feature for rate-limiting and environment variables for dynamic pricing. Ensure the following configurations are set in your .env file:
```env
# Use 'file' if you prefer testing without setting up an additional database
CACHE_STORE=file

# Shipping Calculation Variables (Adjust for production)
SHIPPING_FEE_PER_KM=0.56
MAP_SERVER_URL=https://api.production-map-server.com/v1/distance
MAP_SERVER_JWT=eyJhbGciOiJIUzI1Ni...

# Access Gate Keys
SHIPPING_KEY_ADMIN=C/qLUDR6I85iHlEkZLCHvQ==
SHIPPING_KEY_USER=ex2vK6GQc9QsR011O7UOKA==

```
⚠️ Important Note: Every time you modify the .env file (such as switching the Map Server URL from local to production), you must clear the configuration cache to ensure Laravel reads the latest values. Run this command:

```bash
php artisan config:clear

```

## ⚡ Running the Server (Octane)
This project is configured for high-speed execution using Laravel Octane. Run the following command in your terminal to start the server:
```bash
php artisan octane:start --watch

```
*(Note: Ensure your system has a supported server extension like FrankenPHP or RoadRunner installed).*
## 📚 Endpoint Documentation & Testing
To accelerate testing, client app integration (mobile/web), and to review request/response specifications, please use **Postman**.
 1. Open Postman.
 2. Import the Food Delivery Middleware.postman_collection.json file provided in the docs/ directory of this project.
 3. All necessary Headers (including the X-Idempotency-Key), coordinate Body payloads, and target URLs are pre-configured within the collection.
### Testing Troubleshooting
 * If the API returns a 429 Too Many Requests status, the anti-spam mechanism is active. Wait 60 seconds or clear the cache manually by running: php artisan cache:clear
 * If it returns a 503 Service Unavailable status, double-check that the external server URL and JWT in your .env are correct, and verify that the destination server is currently online.
