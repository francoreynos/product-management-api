# Product Management API

RESTful API developed with Laravel and MySQL to manage products, currencies, and product price history.

## Features

- Complete product CRUD operations (Create, Read, Update, Delete).
- Currency association for each product.
- Product price history with support for different currencies.
- Product tax cost and manufacturing cost management.
- Request validation and JSON responses.
- Eloquent ORM relationships and database migrations.
- Automatic deletion of a product's price history when the product is deleted.
- Automated feature tests covering the main API endpoints and validations.

## Tech Stack

- PHP
- Laravel
- MySQL
- Eloquent ORM
- REST API / JSON

## Requirements

- PHP compatible with the Laravel version specified in `composer.json`.
- Composer
- MySQL
- PHP extensions required by Laravel and the MySQL database driver.

## Installation

1. Clone or download the repository.

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
   php artisan migrate
   php artisan db:seed
   ```

   The database seeder creates the following initial currencies:

   - ARS — Argentine Peso
   - USD — US Dollar
   - EUR — Euro

7. Start the development server:

   ```bash
   php artisan serve
   ```

The API will be available at:

`http://127.0.0.1:8000/api`

## Database Schema

### Currencies

Stores supported currencies.

- `id`
- `code` — three-character currency code.
- `name` — currency name.
- `symbol` — currency symbol.
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

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/products` | List all products |
| POST | `/api/products` | Create a product |
| GET | `/api/products/{id}` | Retrieve a product, its currency, and price history |
| PUT/PATCH | `/api/products/{id}` | Update a product |
| DELETE | `/api/products/{id}` | Delete a product |
| GET | `/api/products/{id}/prices` | List a product's historical prices |
| POST | `/api/products/{id}/prices` | Add a historical price |

## Example Requests

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

The `currency_id` must reference an existing currency. Currency records must be created before products or historical prices can reference them.

## Validation and Responses

- Successful product creation returns HTTP `201 Created`.
- Successful historical price creation returns HTTP `201 Created`.
- Successful read, update, and delete operations return HTTP `200 OK`.
- Invalid input returns HTTP `422 Unprocessable Entity` with JSON validation details.
- Requests for non-existing products return HTTP `404