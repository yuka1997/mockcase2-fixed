# 勤怠管理アプリ

## 環境構築

### Docker ビルド

１． リポジトリをクローン

```bash
git clone git@github.com:coachtech-material/laravel-docker-template.git
mv laravel-docker-template mockcase2_fixed
cd mockcase2_fixed
```

２．Docker 起動

```bash
docker-compose up -d --build
```

### Laravel 環境構築

```bash
docker-compose exec php bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## 使用技術

- PHP 7.4.9
- Laravel 8.83.8
- MariaDB 10.3.39
- Nginx 1.21.1
- Docker / Docker Compose

## テストアカウント

- name:一般ユーザー

  email:user@example.com

  password:user123

- name:一般ユーザー 2

  email:user2@example.com

  password:user123

- name:管理者

  email:admin@example.com

  password:admin123

## ER 図

![ER図](mockcase2.drawio.png)

## URL

- 開発環境: http://localhost/
- phpMyAdmin: http://localhost:8080/
