# WHMCloudFlare

WHM plugin that syncs cPanel account DNS with Cloudflare (create, remove, IP change).

Built using the **LiteSpeed WHM plugin pattern**: Perl CGI entry + Template Toolkit shell + PHP via AJAX.

## Requirements

- cPanel/WHM 11.100+ (WHM 134 tested)
- Cloudflare API Token or Global API Key + email
- Zone must already exist in Cloudflare

## Install

```bash
git clone https://github.com/YOUR_USER/WHMCloudFlare.git
cd WHMCloudFlare
chmod +x install.sh uninstall.sh
sudo ./install.sh
```

WHM: **Plugins → WHMCloudFlare**  
URL: `https://SERVER:2087/cgi/whmcloudflare/whmcloudflare.cgi`

## Uninstall

```bash
sudo ./uninstall.sh
```

## Server layout

| Path | Purpose |
|------|---------|
| `/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare/` | Plugin code, hooks, static |
| `/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare/data/` | Config + logs |
| `/usr/local/cpanel/whostmgr/docroot/templates/whmcloudflare/` | WHM UI template (`.tt`) |
| `whmcloudflare.cgi` | Perl entry → `Content-Type` + WHM master template |
| `index.php` | Settings UI (loaded by AJAX) |

## Hooks

| Event | Action |
|-------|--------|
| `Accounts::Create` | Create/update apex A record |
| `Accounts::Remove` | Delete apex A record |
| `Accounts::SiteIP::set` | Update apex A record |

## Features

- API Token or Global API Key authentication
- Encrypted credential storage
- Auto DNS on account create / remove / IP change
- Proxied (orange cloud) and TTL options
- English / Persian UI
- Test connection + log tail in UI

## Repository layout

```
whmcloudflare/          # Installed to cgi/whmcloudflare/
templates/              # Installed to whostmgr/docroot/templates/whmcloudflare/
appconfig/              # register_appconfig source
LiteSpeedPlugin/        # Reference only
```

## License

MIT
