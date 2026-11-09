#!/bin/bash

ENV_PARAMETER_NAME=ios-auth-key

pwd
aws --region=ap-southeast-2 ssm get-parameter --name /$ENV_PARAMETER_NAME --with-decryption --output text --query "Parameter.Value" > /var/www/api/ios-auth-key.p8
