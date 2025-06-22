# PlugPoint2.0 - C2C Ecommerce Platform

A modern, feature-rich C2C (Consumer-to-Consumer) ecommerce platform inspired by Takealot.com, built with PHP, MySQL, HTML, CSS (Bootstrap), and JavaScript. Features include seller subscriptions, product listings, buyer wishlists, and Paystack payment integration.

## 🌟 Features

### Core Features
- **Multi-Role User System:** Buyers, Sellers, and Admins
- **Product Management:** List, edit, and manage products with categories
- **Advanced Search & Filtering:** Search by name, category, price range, ratings, and seller
- **Shopping Cart & Checkout:** Complete purchase flow with Paystack integration
- **Wishlist System:** Save favorite products for later
- **Product Reviews & Ratings:** 5-star rating system with written reviews
- **Buyer-Seller Messaging:** Real-time messaging system between users
- **Notification System:** Real-time notifications for orders, messages, and updates
- **Admin Dashboard:** Comprehensive admin panel with user, product, and order management
- **Seller Subscriptions:** Tiered subscription plans (Basic, Pro, Enterprise)
- **User Profiles:** Complete user profiles with avatars, contact info, and addresses

### Advanced Features
- **Dark Mode UI:** Modern dark theme with blue/purple gradient
- **Responsive Design:** Mobile-first design that works on all devices
- **Advanced Analytics:** Sales reports, user statistics, and performance metrics
- **Order Management:** Complete order lifecycle from purchase to delivery
- **Product Approval System:** Admin approval for new product listings
- **Category Management:** Organized product categories with filtering
- **Payment Integration:** Secure Paystack payment processing
- **Security Features:** Password hashing, SQL injection protection, XSS prevention

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- cURL extension enabled
- GD extension for image processing

### Local Development Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/PlugPoint2.0.git
   cd PlugPoint2.0
   ```

2. **Set up your web server:**
   - Point your web server to the `PlugPoint2.0` directory
   - Ensure the `assets/images` directory is writable for file uploads

3. **Configure the database:**
   - Create a MySQL database
   - Import the database schema (see Database Setup section)
   - Update database credentials in `includes/db.php`

4. **Configure Paystack:**
   - Sign up for a Paystack account
   - Update your Paystack keys in the relevant files
   - Test keys are included for development

5. **Access the platform:**
   - Navigate to your local server URL
   - Register as a buyer, seller, or admin
   - Start exploring the features!

## 🌐 Hosting on InfinityFree

### Step 1: Prepare Your Files
1. **Clean up development files:**
   - Remove any test data or development configurations
   - Ensure all file paths are relative
   - Check that all images are properly linked

2. **Optimize for hosting:**
   - Compress images for faster loading
   - Remove any local development comments
   - Ensure all files are properly organized

### Step 2: InfinityFree Setup
1. **Create InfinityFree Account:**
   - Go to [infinityfree.net](https://infinityfree.net)
   - Sign up for a free account
   - Verify your email address

2. **Create a New Website:**
   - Log into your InfinityFree control panel
   - Click "New Website"
   - Choose a subdomain or connect your own domain
   - Select PHP 8.0 or higher
   - Note your database credentials

3. **Upload Files:**
   - Use File Manager or FTP to upload your files
   - Upload the entire `PlugPoint2.0` folder contents to `htdocs`
   - Ensure proper file permissions (755 for directories, 644 for files)

### Step 3: Database Configuration
1. **Create Database:**
   - In InfinityFree control panel, go to "MySQL Databases"
   - Create a new database
   - Note the database name, username, and password

2. **Import Database Schema:**
   ```sql
   -- Users table
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255) NOT NULL,
       email VARCHAR(255) UNIQUE NOT NULL,
       password VARCHAR(255) NOT NULL,
       role ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
       phone VARCHAR(20),
       address TEXT,
       avatar VARCHAR(255),
       status ENUM('active', 'suspended') DEFAULT 'active',
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   -- Products table
   CREATE TABLE products (
       id INT AUTO_INCREMENT PRIMARY KEY,
       seller_id INT NOT NULL,
       name VARCHAR(255) NOT NULL,
       description TEXT,
       price DECIMAL(10,2) NOT NULL,
       category VARCHAR(100) NOT NULL,
       image VARCHAR(255),
       status ENUM('active', 'pending', 'rejected') DEFAULT 'pending',
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (seller_id) REFERENCES users(id)
   );

   -- Orders table
   CREATE TABLE orders (
       id INT AUTO_INCREMENT PRIMARY KEY,
       buyer_id INT NOT NULL,
       product_id INT NOT NULL,
       quantity INT DEFAULT 1,
       total DECIMAL(10,2) NOT NULL,
       status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (buyer_id) REFERENCES users(id),
       FOREIGN KEY (product_id) REFERENCES products(id)
   );

   -- Wishlist table
   CREATE TABLE wishlist (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       product_id INT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id),
       FOREIGN KEY (product_id) REFERENCES products(id),
       UNIQUE KEY unique_wishlist (user_id, product_id)
   );

   -- Product reviews table
   CREATE TABLE product_reviews (
       id INT AUTO_INCREMENT PRIMARY KEY,
       product_id INT NOT NULL,
       user_id INT NOT NULL,
       rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
       review TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (product_id) REFERENCES products(id),
       FOREIGN KEY (user_id) REFERENCES users(id)
   );

   -- Subscriptions table
   CREATE TABLE subscriptions (
       id INT AUTO_INCREMENT PRIMARY KEY,
       seller_id INT NOT NULL,
       plan VARCHAR(50) NOT NULL,
       status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
       start_date DATE NOT NULL,
       end_date DATE NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (seller_id) REFERENCES users(id)
   );

   -- Messages table
   CREATE TABLE messages (
       id INT AUTO_INCREMENT PRIMARY KEY,
       sender_id INT NOT NULL,
       receiver_id INT NOT NULL,
       message TEXT NOT NULL,
       is_read BOOLEAN DEFAULT FALSE,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (sender_id) REFERENCES users(id),
       FOREIGN KEY (receiver_id) REFERENCES users(id)
   );

   -- Notifications table
   CREATE TABLE notifications (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       type VARCHAR(50) NOT NULL,
       content TEXT NOT NULL,
       link VARCHAR(255),
       is_read BOOLEAN DEFAULT FALSE,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id)
   );
   ```

3. **Update Database Configuration:**
   - Edit `includes/db.php`
   - Update with your InfinityFree database credentials:
   ```php
   $host = 'your-infinityfree-host';
   $username = 'your-db-username';
   $password = 'your-db-password';
   $database = 'your-db-name';
   ```

### Step 4: Configure Paystack
1. **Update Paystack Keys:**
   - Replace test keys with live keys in:
     - `api/paystack.php`
     - `pages/checkout.php`
     - `pages/checkout_callback.php`

2. **Update Callback URLs:**
   - Change callback URLs to your live domain
   - Test payment flow thoroughly

### Step 5: Final Configuration
1. **Set File Permissions:**
   - `assets/images/` directory: 755
   - All PHP files: 644

2. **Test All Features:**
   - User registration and login
   - Product listing and management
   - Shopping cart and checkout
   - Payment processing
   - Admin functions

## 📁 File Structure

```
PlugPoint2.0/
├── api/
│   └── paystack.php              # Paystack payment API
├── assets/
│   ├── css/
│   │   └── style.css            # Main stylesheet
│   ├── images/                  # Product and user images
│   └── js/
│       └── main.js              # JavaScript functionality
├── includes/
│   ├── auth.php                 # Authentication logic
│   ├── db.php                   # Database connection
│   ├── footer.php               # Site footer
│   └── header.php               # Site header and navigation
├── pages/
│   ├── admin_dashboard.php      # Admin overview
│   ├── admin_orders.php         # Order management
│   ├── admin_products.php       # Product management
│   ├── admin_reports.php        # Analytics and reports
│   ├── admin_users.php          # User management
│   ├── buyer_dashboard.php      # Buyer overview
│   ├── cart.php                 # Add to cart
│   ├── cart_remove.php          # Remove from cart
│   ├── cart_view.php            # View cart
│   ├── checkout.php             # Checkout process
│   ├── checkout_callback.php    # Payment callback
│   ├── dashboard.php            # Role-based dashboard routing
│   ├── index.php                # Homepage
│   ├── login.php                # User login
│   ├── messages.php             # Messaging system
│   ├── orders.php               # Order history
│   ├── product_detail.php       # Product details
│   ├── products.php             # Product listing
│   ├── profile.php              # User profile
│   ├── register.php             # User registration
│   ├── seller_dashboard.php     # Seller overview
│   ├── subscription.php         # Subscription management
│   ├── wishlist.php             # Add to wishlist
│   ├── wishlist_remove.php      # Remove from wishlist
│   └── wishlist_view.php        # View wishlist
└── README.md                    # This file
```

## 🔧 Configuration

### Database Configuration
Edit `includes/db.php`:
```php
$host = 'your-database-host';
$username = 'your-database-username';
$password = 'your-database-password';
$database = 'your-database-name';
```

### Paystack Configuration
Update Paystack keys in relevant files:
```php
$paystack_secret = 'sk_live_your_live_secret_key';
$paystack_public = 'pk_live_your_live_public_key';
```

### File Upload Configuration
Ensure the `assets/images/` directory is writable:
```bash
chmod 755 assets/images/
```

## 🛠️ Customization

### Styling
- Main styles: `assets/css/style.css`
- Bootstrap 5.3.0 included
- Custom CSS variables for easy theming

### Features
- Add new product categories in `pages/add_product.php`
- Modify subscription plans in `pages/subscription.php`
- Customize admin features in admin pages

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF protection on forms
- Input validation and sanitization
- Secure file upload handling

## 📊 Admin Features

- **User Management:** View, edit roles, suspend/activate users
- **Product Management:** Approve, reject, delete products
- **Order Management:** View orders, update status
- **Analytics:** Sales reports, user statistics, revenue tracking
- **System Monitoring:** Platform health and performance

## 🚀 Performance Optimization

- Optimized database queries with proper indexing
- Image compression for faster loading
- Efficient pagination for large datasets
- Caching strategies for frequently accessed data

## 📞 Support

For support and questions:
- Email: support@plugpoint.com
- Phone: 011 557 7756
- Address: 3021 Cling fish street, Sky City, Alberton, 1449

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📈 Future Enhancements

- Mobile app development
- Advanced analytics dashboard
- Multi-language support
- Advanced payment gateways
- AI-powered product recommendations
- Social media integration
- Advanced seller tools
- Bulk import/export features

---

**Built with ❤️ for the South African ecommerce community** 
