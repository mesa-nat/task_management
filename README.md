# Project Setup Guide

This project contains two parts: **Backend** and **Frontend**. Follow the steps below to get both up and running.

---

## 1. Clone the Project

```bash
git clone https://github.com/your-username/your-repo.git

#1. Backend Setup

Open your terminal and navigate to the backend folder:
- cd backend or open folder backend with VS code
- Install PHP dependencies with Composer:
- run: composer install

#2. Environment setup:
Check if there is a .env file.
If not, copy the .env.example file and rename it to .env
Open .env and update the database name (line 14 with DB_DATABASE):
(ex: DB_DATABASE=your_database_name)

#3. Generate the application key:
run: php artisan key:generate

#4. Run database migrations:
run: php artisan migrate

#5. Seed the database (optional if you have seeders):
run: php artisan db:seed

#6. Start the development server:
run: php artisan serve

##2: Frontend Setup

Open your terminal and navigate to the frontend folder:
- cd frontend or open folder frontend with VS code
- Install Node.js dependencies:
- run: npm install

##1: Start the frontend development server:
run: npm run dev

