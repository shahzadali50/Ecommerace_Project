# Laravel E-commerce Project - Complete Index

## 🏗️ Project Overview
A multi-language e-commerce platform built with Laravel 11, Vue.js 3, Inertia.js, and Ant Design Vue. Features include product management, cart functionality, order processing, wishlist, multi-language support, and Stripe payment integration.

## 📁 Project Structure

### Backend (Laravel)

#### 🎯 Core Controllers
- **MainController** (`app/Http/Controllers/MainController.php`)
  - Homepage with products and categories
  - Product detail pages
  - Language switching (EN, PT, JA)
  - Admin dashboard
  - Cache management
  - User role checking

- **CartController** (`app/Http/Controllers/CartController.php`)
  - Add/remove items from cart
  - Wishlist management
  - Cart checkout and payment pages
  - Stock validation

- **OrderController** (`app/Http/Controllers/OrderController.php`)
  - Order generation and processing
  - Order status management
  - Billing details handling
  - Order filtering (today, last 3/7/30 days, by status)

- **ProductController** (`app/Http/Controllers/ProductController.php`)
  - Product CRUD operations
  - Product search functionality
  - Brand-category relationships

- **CategoryController** (`app/Http/Controllers/CategoryController.php`)
  - Category management
  - Category logs

- **BrandController** (`app/Http/Controllers/BrandController.php`)
  - Brand management
  - Brand logs

- **StripeController** (`app/Http/Controllers/StripeController.php`)
  - Stripe payment processing
  - Payment success/cancel handling

#### 🗄️ Models
- **Product** - Core product entity with translations
- **Category** - Product categories with translations
- **Brand** - Product brands with translations
- **Order** - Order management
- **SaleProduct** - Order line items
- **User** - User management
- **Wishlist** - User wishlist functionality
- **Translation Models** - Multi-language support

#### 🔄 Jobs
- **OrderTranslationJob** - Handles order translations
- **TranslateProduct** - Product translation processing
- **TranslateCategory** - Category translation processing
- **TranslateBrand** - Brand translation processing

#### 🛡️ Middleware
- **AdminMiddleware** - Admin access control
- **UserMiddleware** - User access control
- **HandleInertiaRequests** - Inertia.js integration
- **SetLocale** - Language setting

### Frontend (Vue.js + Inertia.js)

#### 🎨 UI Components
- **Ant Design Vue** - Primary UI framework
- **Custom Components** - Reusable Vue components
- **Tailwind CSS** - Utility-first CSS framework

#### 📱 Pages
- **Frontend Pages**
  - Homepage (`frontend/Index.vue`)
  - Product detail (`frontend/products/ProductDetail.vue`)
  - All products (`frontend/products/AllProducts.vue`)
  - Cart checkout (`frontend/cart/CartCheckout.vue`)
  - Cart payment (`frontend/cart/CartPayment.vue`)
  - User orders (`frontend/order/Index.vue`)
  - Wishlist (`frontend/WishlistProduct.vue`)

- **Admin Pages**
  - Dashboard (`admin/Dashboard.vue`)
  - Product management (`admin/product/Index.vue`)
  - Category management (`admin/category/Index.vue`)
  - Brand management (`admin/brand/Index.vue`)
  - Order management (`admin/order/OrderList.vue`)
  - User management (`admin/user/Index.vue`)

#### 🔧 Composables & Stores
- **useAppearance** - Theme management
- **useFlashMessages** - Flash message handling
- **useInitials** - User initials generation
- **dashboardStore** - Admin dashboard state

## 🚀 Key Features

### 🛒 E-commerce Functionality
1. **Product Management**
   - Product CRUD with images
   - Stock management
   - Price calculation (purchase, sale, final price)
   - Category and brand relationships
   - Product translations

2. **Shopping Cart**
   - Session-based cart
   - Add/remove items
   - Quantity management
   - Stock validation
   - Cart persistence

3. **Wishlist**
   - Add/remove products
   - User-specific wishlists
   - Wishlist display

4. **Order Processing**
   - Order generation
   - Billing details collection
   - Payment integration (Stripe)
   - Order status tracking
   - Stock deduction

### 🌐 Multi-language Support
- **Supported Languages**: English (EN), Portuguese (PT), Japanese (JA)
- **Translation System**: Database-based translations
- **Language Switching**: Session-based language switching
- **Translatable Entities**: Products, Categories, Brands, Orders

### 💳 Payment Integration
- **Stripe Integration**: Secure payment processing
- **Payment Methods**: Credit card payments
- **Order Status**: Payment status tracking
- **Error Handling**: Payment failure management

### 👥 User Management
- **Authentication**: Laravel Breeze integration
- **Role-based Access**: Admin and User roles
- **Profile Management**: User profile updates
- **Order History**: User order tracking

### 📊 Admin Dashboard
- **Analytics**: Order statistics
- **Product Management**: Full CRUD operations
- **Order Management**: Status updates and filtering
- **User Management**: User administration
- **Category/Brand Management**: Content organization

## 🔄 Data Flow

### Product Display Flow
1. **Homepage**: Loads categories and products with translations
2. **Product Listing**: Filtered products with pagination
3. **Product Detail**: Individual product with gallery and stock info
4. **Add to Cart**: Stock validation and cart update

### Order Processing Flow
1. **Cart Checkout**: Billing information collection
2. **Payment**: Stripe payment processing
3. **Order Creation**: Database transaction with stock update
4. **Order Confirmation**: User notification and order tracking

### Admin Management Flow
1. **Product Creation**: Form submission with image upload
2. **Translation Processing**: Background job for translations
3. **Order Management**: Status updates and filtering
4. **Analytics**: Dashboard statistics and reports

## 🛠️ Technical Stack

### Backend
- **Laravel 11** - PHP framework
- **MySQL** - Database
- **Inertia.js** - Server-side rendering
- **Stripe** - Payment processing
- **Queue System** - Background job processing

### Frontend
- **Vue.js 3** - JavaScript framework
- **Inertia.js** - Client-side routing
- **Ant Design Vue** - UI components
- **Tailwind CSS** - Styling
- **TypeScript** - Type safety
- **Vite** - Build tool

### Development Tools
- **ESLint** - Code linting
- **Prettier** - Code formatting
- **TypeScript** - Type checking
- **Vue DevTools** - Development debugging

## 📋 Database Schema

### Core Tables
- `users` - User accounts
- `products` - Product information
- `categories` - Product categories
- `brands` - Product brands
- `orders` - Order records
- `sale_products` - Order line items
- `wishlists` - User wishlists

### Translation Tables
- `product_translations` - Product translations
- `category_translations` - Category translations
- `brand_translations` - Brand translations
- `order_translations` - Order translations

### Log Tables
- `product_logs` - Product change history
- `category_logs` - Category change history
- `brand_logs` - Brand change history

## 🔐 Security Features
- **Authentication**: Laravel Breeze
- **Authorization**: Role-based middleware
- **CSRF Protection**: Laravel built-in
- **Input Validation**: Form request validation
- **SQL Injection Protection**: Eloquent ORM
- **XSS Protection**: Vue.js automatic escaping

## 🚀 Deployment Considerations
- **Environment Variables**: Database and API keys
- **Storage Links**: File upload handling
- **Cache Management**: Application optimization
- **Queue Workers**: Background job processing
- **Database Migrations**: Schema management

## 📈 Performance Optimizations
- **Eager Loading**: Relationship optimization
- **Pagination**: Large dataset handling
- **Image Optimization**: WebP format support
- **Caching**: Application and route caching
- **Background Jobs**: Async processing

This index provides a comprehensive overview of the entire Laravel e-commerce project structure, functionality, and technical implementation. 
