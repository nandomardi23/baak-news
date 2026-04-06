#!/bin/bash

# ============================================================
# BAAK News - Production Deployment Script
# ============================================================
# Script ini dijalankan di server production oleh GitHub Actions.
# Pastikan server sudah memiliki PHP 8.2+, Composer, Node.js 22+,
# dan NPM terinstall.
# ============================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}============================================================${NC}"
echo -e "${BLUE}  BAAK News - Starting Deployment...${NC}"
echo -e "${BLUE}  $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${BLUE}============================================================${NC}"

# ──────────────────────────────────────
# Step 1: Enable Maintenance Mode
# ──────────────────────────────────────
echo -e "\n${YELLOW}[1/8] Enabling maintenance mode...${NC}"
php artisan down --retry=60 --refresh=15 || true

# ──────────────────────────────────────
# Step 2: Pull Latest Code
# ──────────────────────────────────────
echo -e "\n${YELLOW}[2/8] Pulling latest code from main...${NC}"
git fetch origin main
git reset --hard origin/main

# ──────────────────────────────────────
# Step 3: Install PHP Dependencies
# ──────────────────────────────────────
echo -e "\n${YELLOW}[3/8] Installing PHP dependencies...${NC}"
composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# ──────────────────────────────────────
# Step 4: Install & Build Frontend Assets
# ──────────────────────────────────────
echo -e "\n${YELLOW}[4/8] Installing Node dependencies...${NC}"
npm ci --production=false

echo -e "\n${YELLOW}[5/8] Building frontend assets...${NC}"
npm run build

# ──────────────────────────────────────
# Step 5: Run Database Migrations
# ──────────────────────────────────────
echo -e "\n${YELLOW}[6/8] Running database migrations...${NC}"
php artisan migrate --force

# ──────────────────────────────────────
# Step 6: Cache & Optimize
# ──────────────────────────────────────
echo -e "\n${YELLOW}[7/8] Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link 2>/dev/null || true

# ──────────────────────────────────────
# Step 7: Restart Queue Workers
# ──────────────────────────────────────
echo -e "\n${YELLOW}[8/8] Restarting queue workers...${NC}"
php artisan queue:restart

# ──────────────────────────────────────
# Step 8: Disable Maintenance Mode
# ──────────────────────────────────────
echo -e "\n${GREEN}Disabling maintenance mode...${NC}"
php artisan up

echo -e "\n${GREEN}============================================================${NC}"
echo -e "${GREEN}  ✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}  $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${GREEN}============================================================${NC}"

# Show deployed version
echo -e "\n${BLUE}Deployed commit:${NC}"
git log -1 --pretty=format:"  %h - %s (%cr by %an)" HEAD
echo ""
