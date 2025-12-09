# Extendable Order and Payment Management API

This Laravel API manages orders and payments with clean architecture and an extensible payment gateway system (strategy pattern). It supports JWT authentication, RESTful endpoints, validation, pagination, and feature/unit tests.

## Setup Instructions
- Requirements: PHP 8.2+, Composer, MySQL
- Environment:
	- Copy `.env.example` to `.env` and set DB and mail settings
	- Important keys: `JWT_SECRET`, gateway configs in database "table payment_methods"

```bash
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
php artisan test # for running tests
php artisan queue:work # for processing jobs (e.g., sending mails)
```

## Authentication
- JWT based
- Endpoints:
	- `POST /api/site/register` → create customer
	- `POST /api/site/login` → issue JWT
	- `POST /api/site/logout` → revoke JWT
	- `GET /api/site/profile` (auth:customer)
	- `PUT /api/site/profile` (auth:customer)

## Orders (Admin and Site)
- Admin routes (auth:admin):
	- `GET /api/admin/orders` → list with pagination, filter by `status`
	- `POST /api/admin/orders/create` → create order (user, items)
	- `PUT /api/admin/orders/{order}` → update
	- `DELETE /api/admin/orders/{order}` → delete (blocked if payments exist)
	- Items: `POST /api/admin/orders/{order}/add-item`, `POST /api/admin/orders/{order}/update-item/{itemId}`, `DELETE /api/admin/orders/{order}/remove-item/{itemId}`
- Site routes (auth:customer):
	- `GET /api/site/orders` → customer’s orders
	- `POST /api/site/orders` → create customer order
	- `GET /api/site/payments` → list payments for customer

### Order Payload
```json
{
	"user_id": 1,
	"status": "pending",
	"items": [
		{ "product_id": 10, "quantity": 2, "price": 99.99 }
	]
}
```
- Server calculates totals via `order_items.qty * order_items.price` and accessor `sub_total`/`grand_total`.

## Payments
- Process payment (example; check controller):
	- `POST /api/site/payments` or `POST /api/admin/orders/{order}/pay` (depending on your controllers)
	- Fields: `payment_id`, `order_id`, `method` (e.g. `paypal`, `stripe`, `youssef`), `status` (`pending`, `success`, `failed`)
- Business rules:
	- Payments can only be processed for orders with status `confirmed`
	- Orders cannot be deleted if associated payments exist
- View payments:
	- `GET /api/site/payments` (customer)
	- Admin can query payments under admin endpoints

## Extensible Gateways (Strategy Pattern)
- Location:
	- Interfaces: `app/Interfaces/PaymentMethodInterface.php`
	- Strategies: `app/Payments/*` (e.g., `PaypalPayment.php`, `StripePayment.php`, `YoussefPayment.php`)
	- Service: `app/Services/PaymentService.php`

- How it works:
	- Controllers call `PaymentService` with `method` (string)
	- `PaymentService` resolves the appropriate strategy (implements `PaymentMethodInterface`) and executes `process(order, payload)`
    - Into database we have `payment_methods` table to store available methods and their config (e.g., required_fields JSON ["client_id","secret"] then we need to add these keys into configuration columns eg. {"secret": "your_secret","client_id": "your_client_id"})

- Add a new gateway in 3 steps:
	1. Insert a record in `payment_methods` with:
	   - `name`, `slug` (e.g., `paypal`), and `class` (short PHP class name like `PaypalPayment`)
	   - `required_fields` array (e.g., `["client_id","secret"]`)
	   - `configuration` JSON (e.g., `{ "client_id": "your_id", "secret": "your_secret" }`)
	2. Create `app/Payments/YourGatewayPayment.php` implementing `PaymentMethodInterface` and read config via `$this->gateway->configuration`. Implement `pay`, `callback`, and `refund`.
	3. Use the gateway `slug` in your payment requests; the service resolves the gateway by the DB entry and instantiates the corresponding class. `.env` keys are optional if you prefer environment variables over DB config.

## Validation & Errors
- FormRequests in `app/Http/Requests` validate inputs
- Meaningful JSON errors with appropriate HTTP status codes (400/422/401/403/404)
- Pagination via standard Laravel paginator (`page`, `per_page`)

## Testing
- Feature tests eg .:
	- Admin orders: `tests/Feature/Api/Admin/OrderTest.php`
	- Site routes: `tests/Feature/Api/Site/SiteRoutesTest.php`
- Run:
```bash
php artisan test
```

## Postman Collection
- A Postman collection should be included under `docs/postman_collection.json` (add yours if not present)
- Import collection and set `{{base_url}}` (e.g., `http://127.0.0.1:8000`)
- Use `{{jwt}}` in `Authorization: Bearer {{jwt}}` after login

## Notes & Assumptions
- Mail sending on order confirmation is triggered (`OrderPaymentMail`) for demo purposes
- Products have soft-delete; orders/items use simple totals without taxes
- Payment processing is simulated; integrate real SDKs in gateway classes as needed

## Adding Gateways – Checklist
- Class implements `PaymentMethodInterface`
- Add tests in `tests/Feature` for gateway success/failure paths

