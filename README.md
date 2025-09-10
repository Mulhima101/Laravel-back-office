# WordPress Back Office (Laravel + Vue + Vuetify)

A Laravel-based back-office system integrated with WordPress.com for blog post management.

## 🚀 Features
- WordPress.com authentication (admin only)  
- Blog post CRUD operations (create, edit, delete)  
- Priority system (Laravel-only feature)  
- Real-time sync with WordPress  
- Vue.js + Vuetify frontend  

## 🛠 Requirements
- PHP 8.2+, Composer  
- Node.js 18+  
- MySQL  

## ⚙️ Installation
```bash
git clone https://github.com/Mulhima101/Laravel-back-office.git
cd wordpress-backoffice
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate
php artisan serve
