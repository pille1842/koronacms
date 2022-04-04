# KoronaCMS -- Community Management System for fraternities

We are developing a community management system for German-language fraternities and sororities.

## Local development setup instructions

You will need the following packages installed on your system:

1. PHP, MySQL (PHP minimum 8.0.2 with extensions: ctype, iconv, pcre, session, simplexml, tokenizer)
2. Composer -- https://getcomposer.org/
3. NodeJS / npm -- https://nodejs.org/
4. Yarn -- https://yarnpkg.com/
5. Symfony CLI -- https://github.com/symfony-cli/symfony-cli

Clone the repository to a suitable location.

Inside the repository, run `composer install` and `yarn install`.

Create a MySQL database and a user. Copy `.env` to `.env.local` and set the database URL:

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"
```

Also create a database with the same name as the main database plus the suffix `_test` (e.g. `korona_test`) and give the database user access to this database. It will be used to run automated tests.

Create the database schema in both databases by running:

```
$ php bin/console database:schema:upgrade --force
$ php bin/console --env=test database:schema:upgrade --force
```

Seed the test database by running:

```
$ php bin/console --env=test hautelook:fixtures:load
```

Run the automated tests to confirm that everything is working:

```
$ php bin/phpunit
```

Finally, start the development server:

```
$ symfony serve
```

You should be able to reach the API documentation by visiting http://localhost:8000/api in your browser.
