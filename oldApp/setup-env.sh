#!/bin/bash
# Запускать на сервере из папки проекта:
#   bash setup-env.sh
# Создаёт/обновляет .env для продакшена.

ENV_FILE=".env"

# Если .env нет — создаём из примера
if [ ! -f "$ENV_FILE" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example "$ENV_FILE"
        echo "Создан .env из .env.example"
    else
        touch "$ENV_FILE"
        echo "Создан пустой .env"
    fi
fi

# Функция: заменяет значение если строка есть, добавляет в конец если нет
set_env() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
    else
        echo "${key}=${value}" >> "$ENV_FILE"
    fi
}

# ─── Приложение ───────────────────────────────────────────
set_env APP_NAME        "KitOper"
set_env APP_ENV         "production"
set_env APP_DEBUG       "false"
set_env APP_URL         "https://schedule.pbk.kz"

set_env APP_LOCALE          "ru"
set_env APP_FALLBACK_LOCALE "ru"
set_env APP_FAKER_LOCALE    "ru_RU"

set_env APP_MAINTENANCE_DRIVER "file"
set_env BCRYPT_ROUNDS          "12"

# ─── Логи ─────────────────────────────────────────────────
set_env LOG_CHANNEL              "stack"
set_env LOG_STACK                "single"
set_env LOG_DEPRECATIONS_CHANNEL "null"
set_env LOG_LEVEL                "warning"

# ─── База данных ──────────────────────────────────────────
set_env DB_CONNECTION "mysql"
set_env DB_HOST       "db"
set_env DB_PORT       "3306"
set_env DB_DATABASE   "kitoper"
set_env DB_USERNAME   "kitoper"
set_env DB_PASSWORD   "kitoper_pass"

set_env MYSQL_ROOT_PASSWORD "rootpass_kitoper"

# ─── Сессии ───────────────────────────────────────────────
set_env SESSION_DRIVER   "database"
set_env SESSION_LIFETIME "120"
set_env SESSION_ENCRYPT  "false"
set_env SESSION_PATH     "/"
set_env SESSION_DOMAIN   "null"

# ─── Очереди / кэш ────────────────────────────────────────
set_env BROADCAST_CONNECTION "log"
set_env FILESYSTEM_DISK      "local"
set_env QUEUE_CONNECTION     "database"
set_env CACHE_STORE          "database"

# ─── Redis ────────────────────────────────────────────────
set_env REDIS_CLIENT   "predis"
set_env REDIS_HOST     "redis"
set_env REDIS_PASSWORD "null"
set_env REDIS_PORT     "6379"

# ─── Почта ────────────────────────────────────────────────
set_env MAIL_MAILER       "log"
set_env MAIL_SCHEME       "null"
set_env MAIL_HOST         "127.0.0.1"
set_env MAIL_PORT         "2525"
set_env MAIL_FROM_ADDRESS '"admin@kitoper.local"'
set_env MAIL_FROM_NAME    '"${APP_NAME}"'

# ─── Ollama ───────────────────────────────────────────────
set_env OLLAMA_HOST "http://ollama:11434"

# ─── Vite ─────────────────────────────────────────────────
set_env VITE_APP_NAME '"${APP_NAME}"'

# ─── APP_KEY: генерируем если не задан ────────────────────
if ! grep -q "^APP_KEY=base64:" "$ENV_FILE"; then
    echo "Генерирую APP_KEY..."
    php artisan key:generate --force
fi

echo ""
echo "✓ .env настроен для продакшена (https://schedule.pbk.kz)"
echo ""
grep -E "^(APP_ENV|APP_DEBUG|APP_URL|LOG_LEVEL)=" "$ENV_FILE"
