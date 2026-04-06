#!/bin/bash

# ============================================================
# BAAK News - Manual Deployment Script
# ============================================================
# Script ini untuk deploy MANUAL di server (tanpa GitHub Actions).
# Jika menggunakan CI/CD, deployment dilakukan otomatis oleh
# GitHub Actions workflow (deploy.yml).
#
# Untuk shared hosting Hostinger, TIDAK perlu install Node.js.
# Build assets dilakukan di GitHub Actions.
# ============================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}============================================================${NC}"
echo -e "${BLUE}  BAAK News - Manual Deployment${NC}"
echo -e "${BLUE}  $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${BLUE}============================================================${NC}"

# ──────────────────────────────────────
# Step 1: Enable Maintenance Mode
# ──────────────────────────────────────
echo -e "\n${YELLOW}[1/6] Enabling maintenance mode...${NC}"
php artisan down --retry=60 --refresh=15 || true

# ──────────────────────────────────────
# Step 2: Pull Latest Code
# ──────────────────────────────────────
echo -e "\n${YELLOW}[2/6] Pulling latest code from main...${NC}"
git fetch origin main
git reset --hard origin/main

# ──────────────────────────────────────
# Step 3: Install PHP Dependencies
# ──────────────────────────────────────
echo -e "\n${YELLOW}[3/6] Installing PHP dependencies...${NC}"
composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

# ──────────────────────────────────────
# Step 4: Run Database Migrations
# ──────────────────────────────────────
echo -e "\n${YELLOW}[4/6] Running database migrations...${NC}"
php artisan migrate --force

# ──────────────────────────────────────
# Step 5: Cache & Optimize
# ──────────────────────────────────────
echo -e "\n${YELLOW}[5/6] Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link 2>/dev/null || true

# ──────────────────────────────────────
# Step 6: Restart Queue & Go Live
# ──────────────────────────────────────
echo -e "\n${YELLOW}[6/6] Restarting queue workers...${NC}"
php artisan queue:restart

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

echo -e "\n${YELLOW}⚠️  NOTE: Build assets (public/build/) harus di-upload${NC}"
echo -e "${YELLOW}   secara terpisah jika tidak menggunakan GitHub Actions CI/CD.${NC}"
