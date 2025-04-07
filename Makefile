### ——————————————————————————————————————————————————————————————————
### —— Local Makefile
### ——————————————————————————————————————————————————————————————————

# Register Toolkit as Symfony Container
SF_CONTAINERS += shopify

include vendor/splash/toolkit/make/toolkit.mk
include vendor/badpixxel/php-sdk/make/sdk.mk
