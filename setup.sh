#!/usr/bin/env bash
# ==============================================================================
# Script Name: setup.sh
# Description: Automated setup script for KayakapMD Consultation Application.
# Validates system prerequisites (Node.js, Composer, PHP >= 8.2), manages .env
# configuration with interactive prompts, executes database migrations and seeders,
# builds frontend assets, and launches both web and asset dev servers concurrently.
#
# Environment Support:
# - Windows Host (Git Bash, WSL, MSYS2)
# - Linux / Docker Container Terminal
# ==============================================================================

# Enable strict mode: exit immediately if a command fails, treat unset variables as error
set -eo pipefail

# Detailed Comment: Determine the absolute directory where this script resides
# so it can be safely executed from any working directory.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# ANSI Color Codes for terminal formatting
COLOR_RESET="\033[0m"
COLOR_BOLD="\033[1m"
COLOR_GREEN="\033[32m"
COLOR_YELLOW="\033[33m"
COLOR_BLUE="\033[34m"
COLOR_RED="\033[31m"
COLOR_CYAN="\033[36m"

echo -e "${COLOR_BOLD}${COLOR_CYAN}=====================================================${COLOR_RESET}"
echo -e "${COLOR_BOLD}${COLOR_CYAN}       KayakapMD Consultation System Setup           ${COLOR_RESET}"
echo -e "${COLOR_BOLD}${COLOR_CYAN}=====================================================${COLOR_RESET}"

# ==============================================================================
# SECTION 1: System Prerequisites Validation
# Checks for Node.js, npm, Composer, and PHP >= 8.2 as requested.
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[1/8] Checking system prerequisites...${COLOR_RESET}"

PREREQ_FAILED=0

# Detailed Comment: Check for Node.js availability
if command -v node >/dev/null 2>&1; then
    NODE_VERSION=$(node -v)
    echo -e "  [✓] Node.js is installed: ${COLOR_GREEN}${NODE_VERSION}${COLOR_RESET}"
else
    echo -e "  [✗] ${COLOR_RED}Node.js is NOT installed or not found in PATH.${COLOR_RESET}"
    PREREQ_FAILED=1
fi

# Detailed Comment: Check for npm availability
if command -v npm >/dev/null 2>&1; then
    NPM_VERSION=$(npm -v)
    echo -e "  [✓] npm is installed: ${COLOR_GREEN}v${NPM_VERSION}${COLOR_RESET}"
else
    echo -e "  [✗] ${COLOR_RED}npm is NOT installed or not found in PATH.${COLOR_RESET}"
    PREREQ_FAILED=1
fi

# Detailed Comment: Parse optional script flags
NO_SERVE=false
for arg in "$@"; do
    case "$arg" in
        --no-serve|--skip-serve|--build-only)
            NO_SERVE=true
            shift
            ;;
    esac
done

# Detailed Comment: Check for Composer availability (supporting composer, composer.bat, or composer.phar)
COMPOSER_CMD=""
if command -v composer >/dev/null 2>&1; then
    COMPOSER_CMD="composer"
elif command -v composer.bat >/dev/null 2>&1; then
    COMPOSER_CMD="composer.bat"
elif [ -f "$SCRIPT_DIR/composer.phar" ]; then
    COMPOSER_CMD="php $SCRIPT_DIR/composer.phar"
fi

if [ -n "$COMPOSER_CMD" ]; then
    COMPOSER_VERSION=$($COMPOSER_CMD --version 2>/dev/null | head -n 1)
    echo -e "  [✓] Composer is installed (${COMPOSER_CMD}): ${COLOR_GREEN}${COMPOSER_VERSION}${COLOR_RESET}"
else
    echo -e "  [✗] ${COLOR_RED}Composer is NOT installed or not found in PATH.${COLOR_RESET}"
    PREREQ_FAILED=1
fi

# Detailed Comment: Check for PHP availability and verify version is >= 8.2
if command -v php >/dev/null 2>&1; then
    PHP_VERSION_RAW=$(php -v | head -n 1)
    if php -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' 2>/dev/null; then
        echo -e "  [✓] PHP version is compatible: ${COLOR_GREEN}${PHP_VERSION_RAW}${COLOR_RESET}"
    else
        echo -e "  [✗] ${COLOR_RED}PHP version is incompatible: ${PHP_VERSION_RAW}${COLOR_RESET}"
        echo -e "      ${COLOR_YELLOW}Requirement: PHP >= 8.2 is required to run Laravel 12.${COLOR_RESET}"
        
        # Detailed Comment: Detect if Docker container 'latest_php_server' with PHP 8.5 is available
        if command -v docker >/dev/null 2>&1 && docker ps --format '{{.Names}}' 2>/dev/null | grep -q "latest_php_server"; then
            echo -e "      ${COLOR_CYAN}Notice: Docker container 'latest_php_server' (PHP 8.5) is currently running.${COLOR_RESET}"
            echo -e "      ${COLOR_CYAN}You can run PHP commands via Docker or execute this script inside the container.${COLOR_RESET}"
        fi
        PREREQ_FAILED=1
    fi
else
    echo -e "  [✗] ${COLOR_RED}PHP is NOT installed or not found in PATH.${COLOR_RESET}"
    PREREQ_FAILED=1
fi

if [ $PREREQ_FAILED -ne 0 ]; then
    echo -e "\n${COLOR_RED}Error: One or more prerequisites are missing or incompatible. Please satisfy the requirements above before running setup.${COLOR_RESET}"
    exit 1
fi

# ==============================================================================
# SECTION 2: Environment Configuration (.env)
# If .env does not exist, copy from .env.example and interactively prompt for:
# DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[2/8] Checking environment configuration (.env)...${COLOR_RESET}"

ENV_CREATED=false

if [ ! -f .env ]; then
    ENV_CREATED=true
    echo -e "  ${COLOR_YELLOW}.env file not found. Creating .env from .env.example...${COLOR_RESET}"

    if [ ! -f .env.example ]; then
        echo -e "  ${COLOR_RED}Error: .env.example does not exist in the project directory!${COLOR_RESET}"
        exit 1
    fi

    cp .env.example .env

    echo -e "\n${COLOR_BOLD}${COLOR_CYAN}Please configure your environment values (Press Enter to use defaults):${COLOR_RESET}"

    # Detailed Comment: Prompt for DB_CONNECTION (default: mysql)
    read -r -p "  Enter DB_CONNECTION [default: mysql]: " INPUT_DB_CONNECTION || true
    DB_CONN="${INPUT_DB_CONNECTION:-mysql}"

    # Detailed Comment: Prompt for DB_HOST (default: 127.0.0.1)
    # Proactively guide user regarding Docker container vs host environment
    echo -e "  ${COLOR_YELLOW}Note: If running inside Docker with a separate database container, consider using 'mysql-db'.${COLOR_RESET}"
    echo -e "  ${COLOR_YELLOW}      If running on host connecting to Docker mapped port, use '127.0.0.1'.${COLOR_RESET}"
    read -r -p "  Enter DB_HOST [default: 127.0.0.1]: " INPUT_DB_HOST || true
    DB_H="${INPUT_DB_HOST:-127.0.0.1}"

    # Detailed Comment: Prompt for DB_PORT (default: 3306)
    read -r -p "  Enter DB_PORT [default: 3306]: " INPUT_DB_PORT || true
    DB_P="${INPUT_DB_PORT:-3306}"

    # Detailed Comment: Prompt for DB_DATABASE (default: kayakapmdv2)
    read -r -p "  Enter DB_DATABASE [default: kayakapmdv2]: " INPUT_DB_DATABASE || true
    DB_D="${INPUT_DB_DATABASE:-kayakapmdv2}"

    # Detailed Comment: Prompt for DB_USERNAME (default: root)
    read -r -p "  Enter DB_USERNAME [default: root]: " INPUT_DB_USERNAME || true
    DB_U="${INPUT_DB_USERNAME:-root}"

    # Detailed Comment: Prompt for DB_PASSWORD (silent when on interactive TTY, direct read when piped)
    if [ -t 0 ]; then
        read -r -s -p "  Enter DB_PASSWORD [default: empty]: " INPUT_DB_PASSWORD || true
        echo ""
    else
        read -r INPUT_DB_PASSWORD || true
    fi
    DB_PASS="${INPUT_DB_PASSWORD:-}"

    # Detailed Comment: Prompt for APP_URL (default: http://localhost:8000)
    read -r -p "  Enter APP_URL [default: http://localhost:8000]: " INPUT_APP_URL || true
    A_URL="${INPUT_APP_URL:-http://localhost:8000}"

    # Detailed Comment: Helper function to safely update key=value in .env file
    update_env_key() {
        local key="$1"
        local value="$2"
        if grep -q "^${key}=" .env; then
            # Replace existing key
            sed -i "s|^${key}=.*|${key}=${value}|" .env
        else
            # Append if missing
            echo "${key}=${value}" >> .env
        fi
    }

    update_env_key "DB_CONNECTION" "$DB_CONN"
    update_env_key "DB_HOST" "$DB_H"
    update_env_key "DB_PORT" "$DB_P"
    update_env_key "DB_DATABASE" "$DB_D"
    update_env_key "DB_USERNAME" "$DB_U"
    update_env_key "DB_PASSWORD" "$DB_PASS"
    update_env_key "APP_URL" "$A_URL"

    echo -e "  [✓] ${COLOR_GREEN}.env configured successfully.${COLOR_RESET}"
else
    echo -e "  [✓] ${COLOR_GREEN}Existing .env file detected. Keeping current configuration.${COLOR_RESET}"
fi

# ==============================================================================
# SECTION 3: Database Connectivity Diagnostics
# Proactively tests MySQL socket/connection to help users identify container/host port mismatches.
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[3/8] Verifying database connectivity...${COLOR_RESET}"

CURRENT_DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2- | tr -d '\r')
CURRENT_DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2- | tr -d '\r')
CURRENT_DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2- | tr -d '\r')
CURRENT_DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2- | tr -d '\r')
CURRENT_DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2- | tr -d '\r')

# Detailed Comment: Test socket reachability using PHP built-in fsockopen
DB_SOCKET_OK=$(php -r "
    \$fp = @fsockopen('${CURRENT_DB_HOST}', (int)'${CURRENT_DB_PORT}', \$errno, \$errstr, 2);
    if (\$fp) {
        fclose(\$fp);
        echo '1';
    } else {
        echo '0';
    }
" 2>/dev/null || echo '0')

if [ "$DB_SOCKET_OK" = "1" ]; then
    echo -e "  [✓] Network socket to ${COLOR_GREEN}${CURRENT_DB_HOST}:${CURRENT_DB_PORT}${COLOR_RESET} is reachable."
else
    echo -e "  [!] ${COLOR_YELLOW}Warning: Could not open socket to ${CURRENT_DB_HOST}:${CURRENT_DB_PORT}.${COLOR_RESET}"
    
    # Detailed Comment: Provide targeted Docker troubleshooting tips
    if [ -f /.dockerenv ]; then
        echo -e "      ${COLOR_YELLOW}Notice: You are running inside a Docker container.${COLOR_RESET}"
        echo -e "      ${COLOR_YELLOW}If MySQL is running in a separate container (e.g. 'mysql-db'), set DB_HOST=mysql-db and DB_PORT=3306 in .env.${COLOR_RESET}"
    else
        # On host, check if Docker container mysql-db is running with a mapped port like 4406
        if command -v docker >/dev/null 2>&1 && docker ps --format '{{.Names}}' 2>/dev/null | grep -q "mysql-db"; then
            echo -e "      ${COLOR_CYAN}Notice: Docker container 'mysql-db' is active!${COLOR_RESET}"
            echo -e "      ${COLOR_CYAN}Port 3306 inside the container may be mapped to port 4406 on the host.${COLOR_RESET}"
            echo -e "      ${COLOR_CYAN}If connecting from host, check if DB_PORT=4406 is needed in .env.${COLOR_RESET}"
        fi
    fi
fi

# ==============================================================================
# SECTION 4: Install PHP Dependencies (composer install)
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[4/8] Running: composer install...${COLOR_RESET}"
$COMPOSER_CMD install

# ==============================================================================
# SECTION 5: Install Node Dependencies (npm install)
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[5/8] Running: npm install...${COLOR_RESET}"
npm install

# ==============================================================================
# SECTION 6: Generate Application Encryption Key (php artisan key:generate)
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[6/8] Running: php artisan key:generate...${COLOR_RESET}"
# Generate key if not already set or if newly created
CURRENT_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2- | tr -d '\r')
if [ -z "$CURRENT_KEY" ] || [ "$ENV_CREATED" = true ]; then
    php artisan key:generate --ansi
else
    echo -e "  [✓] APP_KEY is already set. Skipping regeneration."
fi

# ==============================================================================
# SECTION 7: Database Migration & Conditional Seeder
# Runs php artisan migrate, and if .env was created during this run, executes AdminSeeder.
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[7/8] Running: php artisan migrate...${COLOR_RESET}"
php artisan migrate --force --ansi

# Detailed Comment: Only seed AdminSeeder if .env was freshly created during this script run
if [ "$ENV_CREATED" = true ]; then
    echo -e "\n${COLOR_BOLD}${COLOR_CYAN}New environment detected: running AdminSeeder...${COLOR_RESET}"
    php artisan db:seed --class=AdminSeeder --force --ansi
else
    echo -e "  [✓] Existing environment: skipping AdminSeeder to preserve database state."
fi

# ==============================================================================
# SECTION 8: Build Frontend Assets (npm run build)
# ==============================================================================
echo -e "\n${COLOR_BOLD}${COLOR_BLUE}[8/8] Running: npm run build...${COLOR_RESET}"
npm run build

# ==============================================================================
# SECTION 9: Start Development Servers Concurrently (php artisan serve & npm run dev)
# Starts php artisan serve in the background and npm run dev in the foreground.
# Uses trap to cleanly kill the background server on exit or Ctrl+C.
# ==============================================================================
if [ "$NO_SERVE" = true ]; then
    echo -e "\n${COLOR_BOLD}${COLOR_GREEN}=====================================================${COLOR_RESET}"
    echo -e "${COLOR_BOLD}${COLOR_GREEN}      Setup Complete! (--no-serve flag specified)    ${COLOR_RESET}"
    echo -e "${COLOR_BOLD}${COLOR_GREEN}=====================================================${COLOR_RESET}"
    echo -e "${COLOR_CYAN}To run the servers manually:${COLOR_RESET}"
    echo -e "  1. Laravel Server: ${COLOR_YELLOW}php artisan serve${COLOR_RESET}"
    echo -e "  2. Vite Dev Server: ${COLOR_YELLOW}npm run dev${COLOR_RESET}\n"
    exit 0
fi

echo -e "\n${COLOR_BOLD}${COLOR_GREEN}=====================================================${COLOR_RESET}"
echo -e "${COLOR_BOLD}${COLOR_GREEN}       Setup Complete! Starting Servers...           ${COLOR_RESET}"
echo -e "${COLOR_BOLD}${COLOR_GREEN}=====================================================${COLOR_RESET}"
echo -e "${COLOR_CYAN}Press Ctrl+C at any time to gracefully terminate all services.${COLOR_RESET}\n"

# Detailed Comment: Trap SIGINT (Ctrl+C), SIGTERM, and EXIT to ensure background
# php artisan serve process is cleaned up immediately when the user exits.
SERVE_PID=""
cleanup() {
    echo -e "\n${COLOR_YELLOW}Terminating services...${COLOR_RESET}"
    if [ -n "$SERVE_PID" ] && kill -0 "$SERVE_PID" 2>/dev/null; then
        kill "$SERVE_PID" 2>/dev/null || true
        wait "$SERVE_PID" 2>/dev/null || true
    fi
    # Detailed comment: Remove Vite hot file upon shutdown so Laravel does not remain in dev mode
    # after exit, allowing Apache to seamlessly serve compiled assets via the App URL.
    if [ -f public/hot ]; then
        rm -f public/hot
    fi
    echo -e "${COLOR_GREEN}All processes stopped.${COLOR_RESET}"
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

# Detailed Comment: Launch Laravel built-in web server in background
echo -e "Starting Laravel server (php artisan serve)..."
php artisan serve &
SERVE_PID=$!

# Brief pause to let Laravel server start and bind port
sleep 1

# Detailed Comment: Launch Vite development server in foreground
echo -e "Starting Vite asset server (npm run dev)..."
npm run dev
