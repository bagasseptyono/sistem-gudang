# Sistem Gudang Backend

Sistem Gudang adalah aplikasi yang memungkinkan pengguna untuk mengelola data barang, mutasi, dan pengguna.

## Documentation

Backend API Endpoint Documentation Available in here

[Documentation](https://documenter.getpostman.com/view/25519474/2sAXqwYepX)

## Run Locally

Clone the project

```bash
  git clone https://github.com/bagasseptyono/sistem-gudang.git
```

Go to the project directory

```bash
  cd sistem-gudang
```

Install dependencies

```bash
  composer install
```

Configure file .env and fill database credentials

```bash
  cp .env.example .env
```

Generate Application Key

```bash
  php artisan key:generate
```

Install Migrate Database

```bash
  php artisan migrate
```

Run DB Seeder

```bash
  php artisan db:seed
```

Start the server

```bash
  php artisan serve
```


