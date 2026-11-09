#!/bin/bash

ENV_PARAMETER_NAME=env-prod

if [[ "$DEPLOYMENT_GROUP_NAME" == *-dev-asg ]]
then
    # this only executes when only in development asg
    ENV_PARAMETER_NAME=env-dev
fi

pwd
aws --region=ap-southeast-2 ssm get-parameter --name /$ENV_PARAMETER_NAME --with-decryption --output text --query "Parameter.Value" > /var/www/api/.env

if grep -q '^APP_ENV=production' /var/www/api/.env; then
    grep -q '^APP_DEBUG=false' /var/www/api/.env || { echo "ERROR: APP_DEBUG must be false in production"; exit 1; }
    grep -q '^CORS_ALLOWED_ORIGINS=.\+' /var/www/api/.env || { echo "ERROR: CORS_ALLOWED_ORIGINS must be set in production"; exit 1; }
    grep -q '^TRUSTED_PROXIES=.\+' /var/www/api/.env || { echo "ERROR: TRUSTED_PROXIES must be set in production"; exit 1; }
    grep -q '^TRUSTED_PROXIES=\*' /var/www/api/.env && { echo "ERROR: TRUSTED_PROXIES must not be * in production"; exit 1; }
fi

grep -q '^DEV_CONSOLE_USERNAME=.\+' /var/www/api/.env || echo "WARNING: DEV_CONSOLE_USERNAME is not set — you will not be able to log in to Telescope or Horizon"
grep -q '^DEV_CONSOLE_PASSWORD=.\+' /var/www/api/.env || echo "WARNING: DEV_CONSOLE_PASSWORD is not set — you will not be able to log in to Telescope or Horizon"
