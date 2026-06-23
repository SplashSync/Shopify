

![Splash](http://www.splashsync.com/bundles/theme/img/splash-logo.png)

# Splash Shopify Bundle

This Bundle is source for Embedded Shopify Connector.

## Requirements

* An active Shopify account with Admin rights
* An active Splash Sync User Account for axecuting remote actions

## Documentation

For the configuration guide and reference, see: [Shopify Connector Documentation](https://splashsync.gitlab.io/Shopify/)

## Development Environment

The dev stack is described in `docker-compose.yml`, which aggregates the
per-type service files stored under `docker/` :

| File                          | Services                                         |
|-------------------------------|--------------------------------------------------|
| `docker/toolkit.docker.yaml`  | Splash Toolkit (FrankenPHP) & Toolkit `3.0`      |
| `docker/ngrok.docker.yaml`    | Public HTTPS tunnel to the Toolkit               |

```bash
docker compose up -d
```

### Ngrok Tunnel

`ngrok` exposes the Toolkit (`toolkit.shopify.local`) on a public HTTPS URL so
Shopify can reach local Webhooks / OAuth callbacks.

* Set `NGROK_AUTHTOKEN` in your `.env` file.
* The agent configuration (tunnel definition) lives in `docker/ngrok/ngrok.yml`.
* Local web inspector is available on [http://localhost:4040](http://localhost:4040).

## Contributing

Any Pull requests are welcome! 

This module is part of [SplashSync](https://www.splashsync.com) project.