
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
docker-compose up -d

################################################################################
# PHP 7.4
echo '===> Checks Php 7.4'
docker-compose exec php-7.4 bash ci/install.sh
docker-compose exec php-7.4 php vendor/bin/grumphp run --testsuite=travis
docker-compose exec php-7.4 php vendor/bin/grumphp run --testsuite=csfixer
docker-compose exec php-7.4 php vendor/bin/grumphp run --testsuite=phpstan

################################################################################
# PHP 8.0
echo '===> Checks Php 8.0'
docker-compose exec php-8.0 bash ci/install.sh
docker-compose exec php-8.0 php vendor/bin/grumphp run --testsuite=travis
docker-compose exec php-8.0 php vendor/bin/grumphp run --testsuite=csfixer
docker-compose exec php-8.0 php vendor/bin/grumphp run --testsuite=phpstan
