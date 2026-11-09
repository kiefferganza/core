#!/bin/bash

ENV_PARAMETER_NAME=google-play-credentials

pwd
aws --region=ap-southeast-2 ssm get-parameter --name /$ENV_PARAMETER_NAME --with-decryption --output text --query "Parameter.Value" > /var/www/api/google-play-credentials.json
