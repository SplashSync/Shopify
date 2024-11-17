
################################################################################
#
#  This file is part of SplashSync Project.
#
#  Copyright (C) Splash Sync <www.splashsync.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

################################################################################
# Start Docker Compose Stack
echo '===> Start Docker Stack'
docker compose up -d

################################################################################
# Docker Compose Container you want to check
CONTAINERS="php-8.2,php-8.1"
################################################################################
# Start Docker Compose Stack
echo '===> Start Docker Stack'
docker compose up -d

######################################
# Run Grumphp Test Suites Locally
php wall-e grumphp:full

######################################
# Walk on Docker Compose Container
for ID in $(echo $CONTAINERS | tr "," "\n")
do
    echo "===> Checks $ID"
    # Run Composer Update
    docker compose exec $ID composer update -q || composer update
    # Run Grumphp Test Suites
    docker compose exec $ID wall-e grumphp:full
done
