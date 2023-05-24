1. clone the repo

```
git clone https://github.com/kwarnkham/receipt-backend.git
cd receipt-backend
```

2. fill up env

```
cp .env.example ./.env
nano .env
```

# Update

```
php artisan down
git pull
composer install --optimize-autoloader --no-dev
php artisan migrate --force
npm i
npm run build
php artisan optimize && php artisan view:cache
php artisan up
systemctl restart nginx
```

# Move server

```
cd /etc/nginx/html/
git clone https://github.com/kwarnkham/receipt-backend.git
cd receipt-backend
cp .env.example ./.env
nano .env
composer install --optimize-autoloader --no-dev
php artisan migrate
php artisan storage:link
cd storage/app/public/

move the files

create vh file for nginx

cd /etc/nginx/conf.d/

for ubuntu
sudo chown -R www-data:www-data /etc/nginx/html/receipt-backend/storage /etc/nginx/html/receipt-backend/bootstrap/cache
sudo chmod -R 755 /etc/nginx/html/receipt-backend/storage /etc/nginx/html/receipt-backend/bootstrap/cache

for centos
chown -R nginx:nginx /etc/nginx/html/receipt-backend/storage /etc/nginx/html/receipt-backend/bootstrap/cache
chmod -R 0777 /etc/nginx/html/receipt-backend/storage
chmod -R 0775 /etc/nginx/html/receipt-backend/bootstrap/cache

backup db

scp receipt.dump root@coffee.book-mm.com:/root/
mysql receipt < /root/receipt.dump

php artisan optimize && php artisan view:cache

systemctl restart nginx
```
