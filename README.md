# Pour-Over Coffee Tracker

A lightweight web application built with vanilla PHP, MySQL/MariaDB, and custom CSS to log coffees, dial in brew parameters, and track pour-over brew history.

---

## Features

- **Bean Management:** Track bean names, roasters, roast profiles (Light, Medium, Dark), processing methods, and tasting notes.
- **Brew Logging:** Log doses (grams), water weight, water temperatures (°F), grind sizes, bloom duration, and brew duration.
- **Brew History & Ratings:** Review past extractions sorted by date, edit previous entries, and score brews on a 1–5 scale.
- **User Authentication:** Password hashing using standard Bcrypt (`PASSWORD_BCRYPT`, cost factor 12) with session-based route protection.
- **Responsive UI:** CSS Grid and Flexbox layout styled with modern typography and full accessibility support.

---

## Tech Stack

- **Backend:** PHP 8.x (native PDO)
- **Database:** MySQL / MariaDB
- **Frontend:** Vanilla HTML5, CSS3

---

## Project Structure

```text
├── assets/
│   └── coffee.css              # Custom responsive stylesheet
├── delete/
│   ├── deleteBrew.php          # Brew deletion handler
│   └── deleteCoffee.php        # Coffee deletion handler
├── includes/
│   ├── auth.php                # Session authentication checks
│   ├── brewNav.php             # Authenticated navigation bar
│   ├── db_connect.example.php  # Database connection template
│   ├── helpers.php             # Input validation, sanitization, formatters
│   └── signInNav.php           # Guest/Public navigation bar
├── brewHistory.php             # Full user brew log
├── coffees.php                 # Coffee inventory & creation
├── editBrew.php                # Brew modification form
├── errors.php                  # Global error display
├── index.php                   # Landing page
├── login.php                   # Authentication login form
├── logout.php                  # Session termination
├── newBrew.php                 # Pour-over parameter entry form
├── newUser.php                 # User registration form
├── schema.sql                  # Database table definitions
└── README.md
```

# Setup & Installation

1. Clone the repository
Bash

```
git clone [https://github.com/](https://github.com/)<your-username>/coffee-tracker.git
cd coffee-tracker
```

2. Configure the database

Import schema.sql into your MySQL/MariaDB server:
```Bash
mysql -u root -p < schema.sql
```

3. Set up database credentials

Copy the template configuration file:
```Bash
cp includes/db_connect.example.php includes/db_connect.php
```

Open includes/db_connect.php in your text editor and update your host, database name, user, and password:
PHP

$host     = '127.0.0.1';
$dbName   = 'wp_miniSite'; // Or your local database name
$user     = 'your_db_user';$password = 'your_db_password';

4. Run the application

Start the built-in PHP development server:
```Bash
php -S localhost:8000
```
Navigate to http://localhost:8000 in your web browser.
