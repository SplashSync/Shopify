# Shopify Connector — OAuth2

This connector is **only** responsible for the OAuth2 dialog with Shopify. Everything
related to *installing* a shop (creating the Splash profile / user) is handled
**application side**.

## Surface

The connector exposes exactly **two OAuth2 responsibilities**, both orchestrated by
`Splash\Connectors\Shopify\Services\OAuth2Manager`:

```
                    /ws/shopify            (route splash_connector_action_master)
                          │
                   OAuth2Master  ── ?code present ? ──► OAuth2Manager::saveToken()   [CALLBACK]
                   (dispatcher)   └─ else ───────────► OAuth2Manager::connect($shop) [AUTHORIZE]

  /ws/shopify/{wsid}/secured/connect ─► OAuth2Connect ─► OAuth2Manager::connect()    [AUTHORIZE]
```

- **Authorize** — `OAuth2Manager::connect()` configures the `ShopifyAdapter` (League provider,
  via KnpU) and redirects the user to the Shopify consent screen.
- **Callback** — `OAuth2Manager::saveToken()` identifies the connector by `shop` host, configures
  the client (which selects the right credentials, see below), validates the request HMAC **with
  that client's secret**, exchanges the `code` for a **permanent offline access token**, stores it
  in the `Token` parameter, and renders the `@Shopify/OAuth2/connected.html.twig` confirmation page.

The Shopify offline access token never expires and Shopify returns **no** `refresh_token` — there
is therefore **no token refresh** in this connector.

## Public vs Private/Custom App clients

Two KnpU clients are registered (in `ShopifyExtension`) with the **same** config:

| Client code (`ShopifyAdapter`) | Credentials |
|---|---|
| `CLIENT_CODE` = `shopify` | Public App, from env `SHOPIFY_API_KEY` / `SHOPIFY_API_SECRET` — **never mutated** |
| `CLIENT_CODE_PRIVATE` = `shopify_private` | Private/Custom App, injected at runtime by `ShopifyAdapter::configure()` from the connector `apiKey` / `apiSecret` |

`OAuth2Manager::getClientCode()` picks `shopify_private` when the connector
`hasPrivateAppCredentials()`, else `shopify`. Because the two clients are distinct instances, a
private connector never overwrites the public client's credentials within a session. The HMAC of a
private-app callback is therefore validated with that app's own secret (hence: identify → configure
→ validate, in that order).

**HTTPS redirect_uri.** KnpU computes the `redirect_uri` (`ABSOLUTE_URL`) when the client is first
built, from the request scheme — so the request must be seen as HTTPS. In production traffic is
always HTTPS; in dev behind ngrok this is handled by `config/packages/dev/framework.yaml`
(`trusted_proxies` + `X-Forwarded-Proto`, dev only). No manual scheme forcing in the manager.

## Scopes

The full requested scope list is composed by **`ScopesManagers::getRequiredScopes()`** (the single
authority). `ShopifyAdapter::configure()` and `getMissingScopes()` both go through it — the adapter
just calls `$connector->getRequiredScopes()` (delegated to the manager via `ConnectorScopesTrait`).

| Scope group | Const | Enabled by |
|---|---|---|
| Default | `DEFAULT_SCOPES` | always |
| Logistics | `LOGISTIC_SCOPES` | `hasLogisticMode()` (param `LogisticMode`) |
| Full order history | `ALL_ORDERS_SCOPES` (`read_all_orders`) | `ScopesManagers::hasAllOrders()` (see below) |

**`read_all_orders` is a restricted scope.** `read_orders` only exposes the **last 60 days** of
orders; `read_all_orders` unlocks older orders but **requires Shopify approval**. Requesting it
without approval **breaks the OAuth authorization**. `ScopesManagers::hasAllOrders()` requests it
only when **both** conditions hold:

- `SHOPIFY_READ_ALL_ORDERS=true` — the deployment runs the editor's **approved** App (the validated
  cloud); env injected into `ScopesManagers`, default `false`. Self-hosted deployments leave it false.
- the connector is **not** a Private/Custom App (`!hasPrivateAppCredentials()`) — Shopify does not
  allow a self-created custom App to carry this scope, so it is never requested there.

There is therefore **no user opt-in** for it: a Private App can never have it, and the approved cloud
gets it automatically for its public App.

## Connector ↔ App boundary

The callback only stores a token on a shop that is **already provisioned**
(`identifyByHost($shop)` must succeed). The connector no longer creates any profile/user.

To install a **new** shop, the application must:

1. Own the **App URL** (Shopify entry point), validate the `shop` parameter, and create the
   Splash profile / webservice of type `shopify` with its `WsHost` **before** starting OAuth.
2. Then trigger the connector **Authorize** action; the **Callback** will only store the `Token`
   on the identified connector.
3. Optionally use `ShopifyConnector::fetchShopInformations()` (still available) to enrich the
   profile (owner, email, phone, domain, …).

## What was removed (and where it went)

| Removed element | What it did | Now handled by |
|---|---|---|
| `OAuth2Master::getRegisterToProfile()` | exchange code + `fetchShopInformations` + `registerData` in session `md5(Install::class)` + redirect to `splash_connector_oauth2_install` | **App** (profile creation) |
| `OAuth2Master::getConnectToProfile()` (the `?session=` branch) | redirect to `splash_connector_oauth2_connect` | **App** |
| `OAuth2Refresh` + `ShopifyConnector::refreshAccessToken()` | refresh the token | **Removed** — offline token is permanent |
| `InstallController` | duplicate install entry, never wired | **Removed** — dead code |
| `OAuth2Install` | configure provider + redirect | **Merged** into `OAuth2Manager::connect()` |

Legacy `registerData` shape the app must reproduce for new-shop install (reference):

```php
[
    "username"      => /* shop_owner */,
    "email"         => /* shop email */,
    "phone"         => /* shop phone */,
    "connector"     => "shopify",
    "configuration" => [ "WsHost" => /* shop */, "Token" => /* access token */ ],
    "extras"        => [ "Shop" => /* name */, "Domain" => /* domain */ ],
]
```
