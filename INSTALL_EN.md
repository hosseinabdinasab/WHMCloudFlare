# WHMCloudFlare Installation Guide

## Prerequisites

- WHM/cPanel version 11.80 or higher
- PHP 7.4 or higher
- Root access to the server
- API Token or API Key + Email from Cloudflare
- Internet access for Cloudflare API communication

## Installation Steps

### Method 1: Automated Installation (Recommended) ⭐

The easiest and fastest installation method:

```bash
# 1. Clone or download the project
git clone https://github.com/hosseinabdinasab/WHMCloudFlare.git
cd WHMCloudFlare

# 2. Run the automated installer
chmod +x install.sh
sudo ./install.sh
```

**What the automated installer does:**
- ✅ Checks prerequisites (WHM, PHP, cURL)
- ✅ Creates backup of previous installation (if exists)
- ✅ Creates necessary directories
- ✅ Copies all files
- ✅ Sets proper permissions
- ✅ Creates default configuration
- ✅ Registers WHM hooks
- ✅ Tests PHP syntax
- ✅ Validates installation

**Benefits of automated installation:**
- 🚀 Fast and easy
- 🛡️ Automatic prerequisite checking
- 💾 Automatic backup
- ✅ Testing and validation
- 📊 Progress display with colors

### Method 2: Manual Installation

If you prefer manual installation:

```bash
# 1. Copy project files to appropriate directory
cp -r WHMCloudFlare /usr/local/cpanel/whm/addons/

# 2. Run installation script
cd /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod +x install/install.sh
sudo ./install/install.sh
```

### 3. Set Permissions

**Note:** If you used the automated installer, this step has been done automatically.

```bash
chmod -R 755 /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod 777 /usr/local/cpanel/whm/addons/WHMCloudFlare/logs
chmod 777 /usr/local/cpanel/whm/addons/WHMCloudFlare/config
chmod 777 /usr/local/cpanel/whm/addons/WHMCloudFlare/cache
```

### 4. Get API Token from Cloudflare

1. Log in to your Cloudflare account
2. Go to **My Profile** > **API Tokens**
3. Click **Create Token**
4. Use the **Edit zone DNS** template
5. Select your Zone
6. Copy the token (it will only be shown once!)

**Or** you can use API Key:
- **Email**: Your Cloudflare account email
- **Global API Key**: From **My Profile** > **API Tokens** > **Global API Key**

### 5. Get Zone ID

1. Log in to Cloudflare Dashboard
2. Select your domain
3. In the **Overview** section, find Zone ID on the right side

### 6. Configure in WHM

1. Log in to WHM
2. Go to **Plugins** > **WHMCloudFlare**
3. Enter the following information:
   - **API Token** (or API Email + API Key)
   - **Zone ID**
4. Enable automation settings
5. Click **Save Settings**
6. Click **Test Connection**

### 7. Enable Module

In the settings page, enable the **Enable Module** option.

## Testing Installation

### Verify Installation

After installation, you can verify the installation:

```bash
# Check registered hooks
/usr/local/cpanel/bin/manage_hooks list | grep WHMCloudFlare

# Check installed files
ls -la /usr/local/cpanel/whm/addons/WHMCloudFlare/

# Check logs
tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/*.log
```

### Test Functionality

To test functionality:

1. Create a test account in WHM
2. Check that DNS records have been created in Cloudflare
3. Check logs in the settings page
4. Use the statistics dashboard to view statistics

## Uninstallation

### Method 1: Using Automated Uninstaller

```bash
cd /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod +x install/uninstall.sh
sudo ./install/uninstall.sh
```

**Note:** The uninstaller automatically:
- ✅ Backs up settings
- ✅ Removes WHM hooks
- ✅ Removes files

### Method 2: Manual Uninstallation

```bash
# Remove hooks
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/createacct.php --category Whostmgr --event Accounts::Create
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/removeacct.php --category Whostmgr --event Accounts::Remove
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/changepackage.php --category Whostmgr --event Accounts::ChangePackage
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/setsiteip.php --category Whostmgr --event Accounts::SetSiteIP

# Remove files
rm -rf /usr/local/cpanel/whm/addons/WHMCloudFlare
```

## Troubleshooting

### Issue: Hooks are not executing

```bash
# Check registered hooks
/usr/local/cpanel/bin/manage_hooks list

# Check logs
tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/whmcloudflare-*.log
```

### Issue: Cloudflare connection error

- Verify that API Token or API Key is valid
- Verify that Zone ID is correct
- Verify that server has internet access
- Use the **Test Connection** button in the settings page

### Issue: Records are not being created

- Verify that the module is enabled
- Check logs for errors
- Verify that Zone ID is correct
- Verify that the domain exists in the selected Zone

## Support

To report issues or suggest new features, please create an Issue in the project repository.

