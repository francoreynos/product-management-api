# Product Management API

RESTful API developed with Laravel and MySQL to manage products, currencies, and product price history.

## Features

- Complete product CRUD operations (Create, Read, Update, Delete).
- Currency association for each product.
- Product price history with support for different currencies.
- Currency exchange rate management.
- Product tax cost and manufacturing cost management.
- Token-based authentication using Laravel Sanctum.
- Request validation and JSON responses.
- Eloquent ORM relationships and database migrations.
- Automatic deletion of a product's price history when the product is deleted.
- Protected product endpoints requiring authentication.
- Automated feature tests covering the main API endpoints and validations.

## Tech Stack

- PHP
- Laravel
- MySQL
- Eloquent ORM
- Laravel Sanctum
- REST API / JSON

## Requirements

- PHP compatible with the Laravel version specified in `composer.json`.
- Composer
- MySQL
- PHP extensions required by Laravel and the MySQL database driver.

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/francoreynos/product-management-api.git
   cd product-management-api
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Create a MySQL database, for example `prueba_laravel`.

4. Copy `.env.example` to `.env` and configure the database connection:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=prueba_laravel
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Run the database migrations and seed the initial currencies:

   ```bash
   php artisan migrate --seed
   ```

   The database seeder creates the following initial currencies:

   | Code | Currency | Exchange Rate |
   |---|---|---:|
   | USD | US Dollar | 1.000000 |
   | ARS | Argentine Peso | 1500.000000 |
   | EUR | Euro | 0.920000 |

   **Note:** USD is used as the reference currency. The exchange rates are illustrative initial values and are not automatically updated from an external exchange rate provider.

7. Start the development server:

   ```bash
   php artisan serve
   ```

The API will be available at:

`http://127.0.0.1:8000/api`

## Authentication

The API uses Laravel Sanctum for token-based authentication.

### Register

**POST** `/api/register`

Creates a new user and returns an authentication token.

Example request:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123!"
}
```

### Login

**POST** `/api/login`

Authenticates an existing user and returns an access token.

Example request:

```json
{
  "email": "john@example.com",
  "password": "Password123!"
}
```

### Authenticated Requests

All product and product price endpoints require a valid Bearer Token.

Include the following header in authenticated requests:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Logout

**POST** `/api/logout`

Invalidates the current access token.

Requires authentication.

## Database Schema

### Currencies

Stores supported currencies and their exchange rates.

- `id`
- `code` — three-character currency code (e.g., USD, ARS, EUR).
- `name` — currency name.
- `symbol` — currency symbol.
- `exchange_rate` — exchange rate relative to the reference currency.
- `created_at`, `updated_at`

### Products

Stores product information.

- `id`
- `name` — product name.
- `description` — product description.
- `price` — current product price.
- `currency_id` — references `currencies.id`.
- `tax_cost` — product tax cost.
- `manufacturing_cost` — product manufacturing cost.
- `created_at`, `updated_at`

### Product Prices

Stores historical prices associated with products and currencies.

- `id`
- `product_id` — references `products.id`.
- `price` — historical price.
- `currency_id` — references `currencies.id`.
- `created_at`, `updated_at`

## API Endpoints

All product and product price endpoints require authentication.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Authenticate a user and obtain a token |
| POST | `/api/logout` | Invalidate the current access token |
| GET | `/api/products` | List all products |
| POST | `/api/products` | Create a product |
| GET | `/api/products/{id}` | Retrieve a product, its currency, and price history |
| PUT/PATCH | `/api/products/{id}` | Update a product |
| DELETE | `/api/products/{id}` | Delete a product |
| GET | `/api/products/{id}/prices` | List a product's historical prices |
| POST | `/api/products/{id}/prices` | Add a historical price |

## Example Requests

The following examples assume that an access token has already been obtained through registration or login.

### Create a Product

**POST** `/api/products`

```json
{
  "name": "Example Product",
  "description": "Product description",
  "price": 1500.00,
  "currency_id": 1,
  "tax_cost": 150.00,
  "manufacturing_cost": 500.00
}
```

### Add a Historical Price

**POST** `/api/products/1/prices`

```json
{
  "price": 1800.00,
  "currency_id": 2
}
```

The `currency_id` must reference an existing currency. Currency records are initially created through the database seeder.

## Validation and Responses

The API returns JSON responses and uses standard HTTP status codes.

- `200 OK` — Successful read, update, or delete operation.
- `201 Created` — Successful user registration, product creation, or historical price creation.
- `401 Unauthorized` — Missing or invalid authentication token, or invalid login credentials.
- `404 Not Found` — Requested resource does not exist.
- `422 Unprocessable Entity` — Invalid input or validation errors.

Laravel automatically handles request validation and returns JSON responses containing validation error details.

## Testing

The project includes automated feature tests covering authentication, product management, price history, and validation.

Run the test suite with:

```bash
php artisan test
```

## Security

- Product and price endpoints are protected using Laravel Sanctum.
- Passwords are securely hashed before being stored.
- Input data is validated before database operations.
- Eloquent relationships and database foreign keys maintain data integrity.
- Access tokens can be invalidated through the logout endpoint.
