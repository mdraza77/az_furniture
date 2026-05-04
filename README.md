# Az Furniture

## Description
Az Furniture is an e-commerce web application built with PHP and MySQL, designed for buying and selling furniture. It includes user-facing features for browsing products, managing carts and wishlists, placing orders, and tracking shipments. The admin panel allows managing products, categories, orders, users, and settings.

## Features
- **User Features**:
  - User registration and login
  - Product browsing and search
  - Shopping cart and wishlist management
  - Order placement and history
  - Payment methods (card, UPI)
  - Profile management and delivery addresses
  - Order tracking

- **Admin Features**:
  - Product and category management
  - Order management and status updates
  - User management
  - Settings configuration
  - Tracking updates

- **Additional**:
  - Email notifications via PHPMailer
  - Responsive design with CSS and JS

## Installation
1. **Prerequisites**:
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Composer
   - A web server (e.g., Apache, Nginx) or local development environment like Laragon/XAMPP

2. **Setup**:
   - Clone or copy the project to your web server's root directory (e.g., `c:\laragon\www\Az Furniture`).
   - Navigate to the project directory and run `composer install` to install dependencies.
   - Create a MySQL database and import the schema from `Database backup/az_furniture.sql`.
   - Update `config/database.php` with your database credentials.
   - Ensure the web server is configured to serve PHP files.

3. **Run**:
   - Start your web server and MySQL.
   - Access the application at `http://localhost/Az%20Furniture/` (adjust path as needed).
   - Admin panel: Access via `Admin/index.php` after logging in as an admin user.

## Usage
- **User Side**: Register/login, browse products, add to cart, checkout, and track orders.
- **Admin Side**: Login to manage inventory, orders, and users.

## Technologies Used
- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Libraries**: PHPMailer (via Composer)
- **Development Environment**: Assumes local setup with tools like Laragon