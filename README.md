# Fullstack Pet Shop Task
A fullstack application built with Laravel (on the backend) and Vue.js (on the frontend)

## Project Setup
1. **Create .env File**: Create a file in the root directory and name it `.env`. Copy the content from the `.env.example` and paste into `.env`, and save it.

2. **Install Composer Dependencies**: Run the command

    ```bash
    composer install
    ```

3. **Install NPM Dependencies**: Run the command

    ```bash
    npm install
    ```
4. **Create Database**: Create a database (MySql DB preferably) and name it exactly as specified in the `.env` file "petshop".

5. **Migrate Database**: Run the following command to populate your database with the necessary tables:

    ```bash
    php artisan migrate
    ```

6. **Run Seeder**: Run the command below to populate the users' table with an admin user account (with default credentials `email: admin@buckhill.co.uk` and `password: secret123`), categories, brands, and order_statuses tables with some data for easy testing

    ```bash
    php artisan db:seed
    ```

## Tests
1. Execute the command below to run unit/feature tests

    ```bash
    php artisan test
    ```

For test coverage, you can locate (and open in a browser) the `index.html` file in the `\reports` folder in the root directory

## Run Project
1. Run the following commands in two separate terminals

    ```bash
    php artisan serve
    ```

    ```bash
    npm run dev
    ```
For the frontend application, copy the url generate from `php artisan serve` command and paste it in a browser.

## API docs
To access and test the API endpoints, paste this url in the browser `{{ baseurl }}/api/documentation`

You can login as admin with the credentials: `email: admin@buckhill.co.uk` and `password: secret123`
