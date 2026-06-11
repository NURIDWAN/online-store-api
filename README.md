# Online Store API

Online Store API built with Laravel 12 for Fullstack Engineer Assessment.

## Features

* Product CRUD API
* Create Order API
* Order Item Management
* Inventory Management
* Flash Sale Support
* Race Condition Prevention
* Functional Testing
* JSON API Response

## Tech Stack

* PHP 8.3+
* Laravel 12
* SQLite / MySQL
* PHPUnit

---

## Installation

Clone repository:

```bash
git clone https://github.com/NURIDWAN/online-store-api.git
cd online-store-api
```

Install dependencies:

```bash
composer install
```

Copy environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Configure database in `.env`

```env
DB_CONNECTION=sqlite
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

Start development server:

```bash
php artisan serve
```

Application will be available at:

```text
http://127.0.0.1:8000
```

---

## API Endpoints

### Products

#### Get All Products

```http
GET /api/products
```

#### Get Product By ID

```http
GET /api/products/{id}
```

#### Create Product

```http
POST /api/products
```

Request Body:

```json
{
    "name": "Laptop Gaming",
    "price": 15000000,
    "stock": 10
}
```

#### Update Product

```http
PUT /api/products/{id}
```

#### Delete Product

```http
DELETE /api/products/{id}
```

---

### Orders

#### Create Order

```http
POST /api/orders
```

Request Body:

```json
{
    "product_id": 1,
    "quantity": 1
}
```

Success Response:

```json
{
    "success": true,
    "message": "Order created successfully"
}
```

Error Response:

```json
{
    "success": false,
    "message": "Insufficient stock"
}
```

---

## Race Condition Handling

To prevent negative inventory during flash sales, the application uses:

* Database Transactions
* Row-Level Locking (`lockForUpdate()`)

Example:

```php
DB::transaction(function () {

    $product = Product::lockForUpdate()->findOrFail($productId);

    if ($product->stock < $quantity) {
        abort(422, 'Insufficient stock');
    }

    $product->decrement('stock', $quantity);

});
```

This ensures that concurrent purchase requests cannot reduce product stock below zero.

---

## Running Tests

Run all tests:

```bash
php artisan test
```

Run specific test:

```bash
php artisan test --filter=FlashSaleTest
```

The project includes a functional test to verify that inventory never becomes negative during flash sale scenarios.

---

## Project Structure

```text
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── ProductController.php
│           └── OrderController.php
├── Models/
│   ├── Product.php
│   ├── Order.php
│   └── OrderItem.php

database/
├── migrations/
├── seeders/

routes/
└── api.php
```

---

## Author

M Raka Nuridwan

GitHub:
https://github.com/NURIDWAN
