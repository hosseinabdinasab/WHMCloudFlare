# Quick Start Guide - WHMCloudFlare

## 🚀 Quick Installation (3 Steps)

### Step 1: Download Project

```bash
git clone https://github.com/hosseinabdinasab/WHMCloudFlare.git
cd WHMCloudFlare
```

### Step 2: Run Automated Installer

```bash
chmod +x install.sh
sudo ./install.sh
```

### Step 3: Configure in WHM

1. Log in to WHM
2. Go to **Plugins > WHMCloudFlare**
3. Enter Cloudflare API Token or API Key + Email
4. Set Zone ID
5. Enable the module
6. Save

**Done!** 🎉

---

## 📋 Installation Checklist

- [ ] Project cloned
- [ ] Automated installer executed
- [ ] Hooks registered
- [ ] API Token/Key entered
- [ ] Zone ID configured
- [ ] Module enabled
- [ ] Connection test successful

---

## 🔍 Verify Installation

```bash
# Check hooks
/usr/local/cpanel/bin/manage_hooks list | grep WHMCloudFlare

# Should show 4 hooks:
# - createacct.php
# - removeacct.php
# - changepackage.php
# - setsiteip.php
```

---

## ⚡ Quick Test

1. Create a test account in WHM
2. Check that DNS records have been created in Cloudflare
3. Check logs in the settings page

---

## 🆘 Having Issues?

- [Troubleshooting Guide](docs/TROUBLESHOOTING.md)
- [FAQ](docs/FAQ.md)
- [Full Installation Guide](INSTALL_EN.md)

---

**Estimated Installation Time:** 2-3 minutes

