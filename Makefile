### ——————————————————————————————————————————————————————————————————
### —— Local Makefile
### ——————————————————————————————————————————————————————————————————

# Register Toolkit as Symfony Container
SF_CONTAINERS += toolkit

include vendor/splash/toolkit/make/toolkit.mk
include vendor/badpixxel/php-sdk/make/sdk.mk

.PHONY: serve
serve: 	## Start Local Symfony Server
	symfony serve --no-tls

.PHONY: bridge
bridge: 	## Build Splash Bridge Phar
	php vendor/bin/bridge-builder --native
