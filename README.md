# WHMCloudFlare

WHM plugin that syncs cPanel account DNS with Cloudflare when accounts are created, removed, or change IP.

## Requirements

- cPanel/WHM 11.100+ (tested conceptually for WHM 134)
- PHP with cURL and OpenSSL (cPanel 3rdparty PHP)
- Cloudflare API Token **or** Global API Key + account email
- Domain zone must already exist in Cloudflare

## Install

```bash
git clone https://github.com/hosseinabdinasab/WHMCloudFlare.git
cd WHMCloudFlare
chmod +x install.sh uninstall.sh
sudo ./install.sh
```

In WHM: **Plugins → WHMCloudFlare**

Direct URL: `https://SERVER:2087/cgi/whmcloudflare/`

## Uninstall

```bash
sudo ./uninstall.sh
```

## Layout (on server)

| Path | Purpose |
|------|---------|
| `/var/cpanel/addons/whmcloudflare/` | PHP code, hooks, config, logs |
| `/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare/index.cgi` | WHM entry (shell → PHP UI) |
| `appconfig/whmcloudflare.conf` | Registered via `register_appconfig` |

## Hooks

| Event | Action |
|-------|--------|
| `Accounts::Create` | Create/update apex A record |
| `Accounts::Remove` | Delete apex A record |
| `Accounts::SiteIP::set` | Update apex A record |

Hook JSON fields are read from root or nested `data` (cPanel standard).

## Configuration

Settings file: `/var/cpanel/addons/whmcloudflare/config/settings.json`

API secrets are encrypted at rest. Logs: `/var/cpanel/addons/whmcloudflare/logs/whmcloudflare.log`

## Development

Repository structure mirrors server install targets. `LiteSpeedPlugin/` is a reference WHM plugin (LiteSpeed) for layout comparison only.

## License

MIT
