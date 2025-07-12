# Symfony Backend

## 🔧 Setup Instructions
1. Clone the repository.
2. Run `composer install`
3. Copy `.env` to `.env.local` and configure your DB credentials
4. Run migrations:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
5. Run the Symfony server:
   ```bash
   symfony server:start --no-tls
   ```
