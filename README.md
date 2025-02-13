# Order Management System

Welcome to the **Order Management System**! This project is built using **Laravel Sail** for the backend, **Sanctum** for API authentication, and **ReactAdmin** for the frontend. It allows you to manage orders, products, and stock levels efficiently.

---

## Table of Contents

1. [Features](#features)
2. [Technologies Used](#technologies-used)
3. [Prerequisites](#prerequisites)
4. [Setup Instructions](#setup-instructions)
   - [Backend Setup](#backend-setup)
   - [Frontend Setup](#frontend-setup)
5. [Running the Project](#running-the-project)
   - [Backend](#backend)
   - [Frontend](#frontend)
6. [Running Tests](#running-tests)
7. [API Documentation](#api-documentation)
8. [Troubleshooting](#troubleshooting)
9. [Contributing](#contributing)
10. [License](#license)

---

## Features

- **Order Management**: Create, read, update, and delete orders.
- **Product Management**: Manage products and track stock levels.
- **Stock Validation**: Prevent orders from exceeding available stock.
- **Authentication**: Secure API endpoints using Laravel Sanctum.
- **ReactAdmin Frontend**: A user-friendly interface for managing orders and viewing products.

---

## Technologies Used

- **Backend**:
  - Laravel Sail (Dockerized Laravel environment)
  - Laravel Sanctum (API authentication)
  - MySQL (Database)
  - PHPUnit (Testing)

- **Frontend**:
  - ReactAdmin (Admin interface)
  - Vite (Build tool)
  - Axios (HTTP client)

- **Other Tools**:
  - Composer (PHP dependency management)
  - npm (JavaScript dependency management)
  - Docker (Containerization)

---

## Prerequisites

Before you begin, ensure you have the following installed:

- **Docker**: [Install Docker](https://docs.docker.com/get-docker/)
- **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/)
- **Node.js**: [Install Node.js](https://nodejs.org/)
- **Composer**: [Install Composer](https://getcomposer.org/)

---

## Setup Instructions

### Backend Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/diaahere89/lara-api.git
   cd lara-api
    ```

2. **Install PHP Dependencies**:
   ```bash
   ./vendor/bin/sail composer install
    ```

3. **Set Up Environment Variables**:
   - Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```

    - Update the `.env` file with your database credentials and other settings.
    ```bash
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

    - Generate an application key:
    ```bash
    ./vendor/bin/sail artisan key:generate
    ```
    - Run database migrations:
    ```bash
    ./vendor/bin/sail artisan migrate
    ```
    - Seed the database with sample data:
    ```bash
    ./vendor/bin/sail artisan db:seed
    ```

    - Install Sanctum:
    ```bash
    ./vendor/bin/sail artisan sanctum:install
    ./vendor/bin/sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
    ```

    - Start the Laravel Sail development server:
    ```bash
    ./vendor/bin/sail up
    ```

### Frontend Setup

1. **Navigate to the Frontend Directory**:
   ```bash
   cd app-react
    ```
2. **Install Node.js Dependencies**:
   ```bash
   npm install
   ```

3. **Set Up Environment Variables**:
   - Copy the `.env.example` file to `.env`:
    ```bash
    touch .env
    ```
    - Add the following variables:
    ```bash
    VITE_API_URL=http://localhost:80/api
    ```

## Running the Project
### Backend
   - Start the Laravel Sail development server:
     ```bash
     ./vendor/bin/sail up
     ```

   - Access the backend API at `http://localhost:80`.

### Frontend
   - Start the Development Server: 
   ```bash
   npm run dev
   ```

   - Access the ReactAdmin frontend at `http://localhost:5173`.

## Running Tests
To run the unit tests, use the following command:
```bash
./vendor/bin/sail test
```


## API Documentation

The API endpoints are documented using **Postman** or **Swagger**. You can access the documentation at:

- **Postman Collection**: [Download Postman Collection](https://documenter.getpostman.com/view/10642536/2sAYXCmKYK)

### Available Endpoints

#### Orders
- **GET /api/orders**: List all orders.
- **GET /api/orders/{id}**: Get details of a specific order.
- **POST /api/orders**: Create a new order.
- **PUT /api/orders/{id}**: Update an existing order.
- **DELETE /api/orders/{id}**: Delete an order.

#### Products
- **GET /api/products**: List all products.

#### Authentication
- **POST /api/login**: Authenticate a user and retrieve an access token.
- **POST /api/logout**: Revoke the user's access token.
<!-- - **POST /api/register**: Register a new user. -->

### Example Requests

#### Create an Order
```bash
curl -X POST http://localhost:80/api/orders \
-H "Content-Type: application/json" \
-H "Authorization: Bearer <your-token>" \
-d '{
    "name": "New Order",
    "description": "This is a test order",
    "products": [
        {
            "product_id": 1,
            "quantity": 5
        }
    ]
}'
```

## Troubleshooting

### Common Issues

1. **Docker Containers Not Starting**:
   - Ensure Docker is running.
   - Run `docker-compose up -d` to start the containers manually.

2. **Database Connection Issues**:
   - Verify the database credentials in the `.env` file.
   - Ensure the MySQL container is running.

3. **Frontend Not Connecting to Backend**:
   - Ensure the `VITE_API_URL` in the frontend `.env` file matches the backend URL.

4. **401 Unauthorized Errors**:
   - Ensure the user is authenticated and the correct token is included in API requests.
   - Verify that Laravel Sanctum is properly configured.

5. **Missing `order_product` Table**:
   - Run the migration to create the `order_product` pivot table:
     ```bash
     ./vendor/bin/sail artisan migrate
     ```

---

## Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository.
2. Create a new branch: `git checkout -b feature/your-feature-name`.
3. Commit your changes: `git commit -m 'Add some feature'`.
4. Push to the branch: `git push origin feature/your-feature-name`.
5. Submit a pull request.

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.