#!/bin/bash
# Build script for Korona Community Management System

if [[ ! -x "$(which dialog)" ]]; then
    echo "You need to install dialog." >&2
    exit 1
fi

rm -rf temp
mkdir temp
rm -rf build
mkdir -p build/upload

git clone https://git.koronacms.de/korona-team/koronacms-backend.git temp/koronacms-backend
git clone https://git.koronacms.de/korona-team/koronacms-frontend.git temp/koronacms-frontend

# Backend
pushd temp/koronacms-backend
composer install --no-dev

cp ../../source/dotenv .env.local
dbuser=$(dialog --title "Database username" --inputbox "Enter the database username:" 8 40 3>&1 1>&2 2>&3 3>&-)
dbpassword=$(dialog --title "Database password" --passwordbox "Enter the database password:" 8 40 3>&1 1>&2 2>&3 3>&-)
dbdatabase=$(dialog --title "Database name" --inputbox "Enter the database name:" 8 40 3>&1 1>&2 2>&3 3>&-)
sed -i -e "s/DBUSER/$dbuser/" -e "s/DBPASSWORD/$dbpassword/" -e "s/DBDATABASE/$dbdatabase/" .env.local

php bin/console doctrine:schema:update --force

adminpassword=$(dialog --title "Admin password" --passwordbox "Enter password for admin:" 8 40 3>&1 1>&2 2>&3 3>&-)
hash=$(php bin/console security:hash-password --no-interaction "$adminpassword" 'App\Entity\User' | grep 'Password hash' | cut -d' ' -f7)
cp ../../source/fixtures.sql .
sed -i -e "s#ADMINPASSWORD#$hash#" fixtures.sql
MYSQL_PWD="$dbpassword" mysql -u "$dbuser" -D "$dbdatabase" <fixtures.sql
rm fixtures.sql

MYSQL_PWD="$dbpassword" mysqldump -u "$dbuser" "$dbdatabase" >../../build/database.sql

cp -r .env bin composer.json composer.lock config migrations public src symfony.lock templates var vendor ../../build/upload

popd

# Frontend
pushd temp/koronacms-frontend
npm install
npm run build

pushd dist
cp -r css fonts img js ../../../build/upload/public
cp index.html ../../../build/upload/templates/frontend.html
popd

# Finish
popd

dialog --title "Ready!" --infobox "Your new build is ready. Upload all the files in build/upload/ and execute the database statements in build/database.sql" 8 40

rm -rf temp
