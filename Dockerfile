# On part de l'image officielle PHP 8.3 avec FPM (FastCGI Process Manager)
# FPM est indispensable pour fonctionner avec Nginx
FROM php:8.3-fpm

# ─── Dépendances système ───────────────────────────────────────────────────────
# On installe tout en une seule commande RUN pour minimiser le nombre de "layers"
# Docker (chaque RUN crée une couche dans l'image)
RUN apt-get update && apt-get install -y \
		tesseract-ocr \
		tesseract-ocr-fra \
		tesseract-ocr-eng \
		poppler-utils \
		ghostscript \
		libpng-dev \
		libjpeg-dev \
		libzip-dev \
		libonig-dev \
		git \
		unzip \
		curl \
        && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
        && apt-get install -y nodejs \
        && rm -rf /var/lib/apt/lists/*

# ─── Extensions PHP ────────────────────────────────────────────────────────────
RUN docker-php-ext-install \
		pdo_mysql \
		zip \
		gd \
		mbstring \
		opcache

# ─── Composer ──────────────────────────────────────────────────────────────────
# On copie Composer depuis son image officielle plutôt que de l'installer manuellement
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ─── Code de l'application ─────────────────────────────────────────────────────
WORKDIR /var/www/html

# Puis on copie tout le reste du projet
COPY . .

# Entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
COPY entrypoint-queue.sh /usr/local/bin/entrypoint-queue.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/entrypoint-queue.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
