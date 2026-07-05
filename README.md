# WHMCloudFlare

Full WHM + cPanel Cloudflare integration: server automation, per-user accounts, DNS management, proxy toggle, sync, and monitoring.

## Features

### WHM (root)
- Server-wide Cloudflare API credentials (fallback)
- Allow users to connect their own Cloudflare account
- Auto DNS on account create / remove / IP change (hooks)
- List connected users, manual sync, logs

### cPanel (each user)
- Connect personal Cloudflare API Token or Global Key
- Dashboard with sync status
- DNS record list, delete records
- Toggle orange-cloud proxy per record
- Zone monitoring (status, plan, 7-day analytics when API allows)

## Install

```bash
chmod +x install.sh uninstall.sh
sudo ./install.sh
```

- **WHM:** Plugins → WHMCloudFlare  
- **cPanel:** search **Cloudflare** in user panel

## Architecture

LiteSpeed-style WHM shell (`whmcloudflare.cgi` + Template Toolkit) + PHP backends.

| Path | Role |
|------|------|
| `cgi/whmcloudflare/` | All PHP code, hooks, data |
| `data/users/{cpuser}/` | Per-user encrypted Cloudflare config |
| `templates/whmcloudflare/` | WHM UI template |
| `frontend/jupiter/whmcloudflare/` | cPanel user plugin |

## API token permissions (recommended)

- Zone:Read, DNS:Edit, Analytics:Read (for monitoring)

## License

MIT
