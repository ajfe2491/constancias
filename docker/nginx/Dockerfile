FROM nginx:1.27

# Copy configuration
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy static assets from the current directory (context is root of repo)
COPY ./public /var/www/html/public
