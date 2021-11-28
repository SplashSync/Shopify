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

# Setup Travis
curl -s https://raw.githubusercontent.com/BadPixxel/Php-Sdk/main/ci/configure.sh  | sh
# Deploy Symfony Files
curl -s https://raw.githubusercontent.com/BadPixxel/Php-Sdk/main/symfony/deploy.sh | sh
# Setup Symfony Version & Create Database
curl -s https://raw.githubusercontent.com/BadPixxel/Php-Sdk/main/symfony/configure.sh | sh
# Install Symfony
curl -s https://raw.githubusercontent.com/BadPixxel/Php-Sdk/main/symfony/install.sh | sh