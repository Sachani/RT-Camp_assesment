#!/bin/bash

# Get PHP path
PHP_PATH=$(command -v php)

# Get the directory of the current script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Set PHP script and log file paths
CRON_PHP="$SCRIPT_DIR/cron.php"
LOG="$SCRIPT_DIR/cron.log"

# Check if cron is installed
if ! command -v crontab &> /dev/null; then
  echo "Error: crontab is not installed. Please install it first."
  exit 1
fi

# Remove existing cron entry for the same PHP script (if any)
crontab -l 2>/dev/null | grep -v "$CRON_PHP" > mycron || true

# Add the new cron job
echo "0 8 * * * $PHP_PATH $CRON_PHP >> $LOG 2>&1" >> mycron

# Install the new cron job
crontab mycron
rm mycron

echo "✅ Cron job installed: $PHP_PATH $CRON_PHP"