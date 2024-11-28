# BILLAPP API

BILLAPP is an API platform designed to manage user wallets, transactions, and airtime purchases. The application enables users to fund their wallets, view transaction histories, and purchase airtime directly through the wallet.

## Features

- **User Authentication**: Register, login, and logout functionality with token-based authentication (using Sanctum).
- **Wallet Management**: Fund user wallets, check balance, and track wallet transactions.
- **Transaction History**: View detailed transactions, including wallet funding and airtime purchases.
- **Airtime Purchases**: Users can purchase airtime using their wallet balance.
- **Secure Authentication**: Token-based system ensuring secure API access.

## Technologies Used

- **Laravel 10.x**: PHP framework used for building the API.
- **Laravel Sanctum**: For token-based authentication.
- **MySQL**: Database for storing user data, wallets, and transaction information.
- **Laravel Eloquent ORM**: For easy database interactions and relationships.

## Installation

1. **Clone the repository**:

    ```bash
    git clone https://github.com/youngyusuff6/bill-app.git
    cd bill-app
    ```

2. **Install dependencies**:

    ```bash
    composer install
    ```

3. **Set up environment file**:

    ```bash
    cp .env.example .env
    ```

4. **Generate application key**:

    ```bash
    php artisan key:generate
    ```

5. **Set up database**: Update your `.env` file with the correct database configuration.

6. **Migrate the database**:

    ```bash
    php artisan migrate
    ```

7. **Run the application**:

    ```bash
    php artisan serve
    ```

## Authentication

Authentication is handled via **Laravel Sanctum**. To use the API, a valid token is required. Users can register and log in to receive a token that must be sent in the `Authorization` header for protected routes.

### Example of a successful login response:

```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "1|iIQLSXB8j7Nho5HrCmZre4g7vnf3EoVVCzJvDIUZ64ac283f"
    }
}
```

## API Endpoints

### Authentication Routes
- **POST** `/api/register` - Register a new user.
- **POST** `/api/login` - User login (returns a token).
- **POST** `/api/logout` - Log out the user and delete their tokens.

### Wallet Routes (Authenticated)
These routes require authentication using a token.

- **GET** `/api/wallet/balance` - Check wallet balance.
- **POST** `/api/wallet/fund` - Fund the user's wallet.

### Transaction Routes (Authenticated)
- **GET** `/api/wallet/transactions/all` - View all transactions.
- **GET** `/api/wallet/transactions/deposit` - View wallet fund transactions (type: FUND).
- **GET** `/api/wallet/transactions/purchases` - View airtime purchase transactions (type: AIRTIME).

### Airtime Routes (Authenticated)
- **GET** `/api/airtime/providers` - Get supported airtime providers.
- **POST** `/api/wallet/airtime` - Purchase airtime.

## Example Requests

### Register a user:

```bash
POST /api/register
```

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secretpassword",
    "password_confirmation": "secretpassword"
}
```

### Login a user:

```bash
POST /api/login
```

```json
{
    "email": "john@example.com",
    "password": "secretpassword"
}
```

### Fund Wallet:

```bash
POST /api/wallet/fund
Authorization: Bearer {your_token}
```

```json
{
    "amount": 500
}
```

### Airtime Purchase:

```bash
POST /api/wallet/airtime
Authorization: Bearer {your_token}
```

```json
{
    "amount": 100,
    "phone_number": "08035284798",
    "provider": "MTN"
}
```
## BILLAPP API Documentation

You can find the full API documentation and Postman collection below:

- [BILLAPP API Documentation (Postman)](https://documenter.getpostman.com/view/19899859/2sAYBXBWd9)




## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
