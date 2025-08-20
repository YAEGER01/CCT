# 🍔 Food Ordering Project — Local Setup Guide

This guide will help you run the project locally **without using XAMPP, WAMP, or MAMP**.

---

## 🚀 How to Run Locally

1. **Open the terminal in your IDE**
   (For example, VS Code’s integrated terminal.)

2. **Start PHP’s built-in development server**

   ```bash
   php -S localhost:8000
   ```

3. **Check the output for the development server URL**
   Example:

   ```
   [Wed Aug 20 22:12:06 2025] PHP 8.4.11 Development Server (http://localhost:8000) started
   ```

   ✅ If you see a similar message, your server is running successfully!

4. **Open your browser** and go to the URL shown (`http://localhost:8000`) to access the project.

---

## 🗄 Configure the Database

1. **Create a new database** in MariaDB for the project:

   ```sql
   CREATE DATABASE food_ordering_db;
   ```

2. **Import the SQL file** containing all the necessary tables and data.
   Place the `.sql` file anywhere in your project folder. Then run:

   ```bash
   cd food_ordering_project
   mysql -u root -p < food_ordering_db.sql
   ```

   Enter your MariaDB password when prompted.

3. **Update database connection** in your project if necessary.
   Make sure your PHP files point to the correct username, password, and database name.

---

🎉 Your project is now running locally with the database ready. You can start testing all features immediately.
