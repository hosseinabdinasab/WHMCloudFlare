# WHMCloudFlare - Automated DNS Management Module for WHM

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![WHM Version](https://img.shields.io/badge/WHM-11.80%2B-orange.svg)](https://cpanel.net/)

## 📋 Description

WHMCloudFlare is a powerful addon for WHM/cPanel that automatically creates and manages DNS records required by the server in Cloudflare.

**Author:** [Hossein Abdinasab](https://github.com/hosseinabdinasab)  
**GitHub:** https://github.com/hosseinabdinasab/WHMCloudFlare

## ✨ Features

- ✅ Automatic DNS record creation when creating new accounts
- ✅ Automatic record deletion when removing accounts
- ✅ Automatic IP update when account IP changes
- ✅ Support for A, AAAA, CNAME, MX, TXT records
- ✅ Simple user interface for settings
- ✅ Complete logging of all operations
- ✅ Support for multiple Zones in Cloudflare
- ✅ Automatic SSL/TLS management
- ✅ **Multi-language support (Persian & English)**
- ✅ Advanced security (encryption, audit logging)
- ✅ Performance optimization (cache, retry mechanism)
- ✅ Statistics dashboard
- ✅ Email notifications

## 📁 Project Structure

```
WHMCloudFlare/
├── hooks/              # WHM Hooks
├── lib/                # Main classes
├── ui/                 # User interface
├── cpanel/             # cPanel integration
├── config/             # Configuration files
├── logs/               # Log files
├── lang/               # Language files (fa.php, en.php)
└── install/            # Installation scripts
```

## 🚀 Installation

### Method 1: Clone from GitHub

```bash
cd /usr/local/cpanel/whm/addons/
git clone https://github.com/hosseinabdinasab/WHMCloudFlare.git
cd WHMCloudFlare
chmod +x install/install.sh
./install/install.sh
```

### Method 2: Manual Download and Install

1. Download the project from [GitHub](https://github.com/hosseinabdinasab/WHMCloudFlare)
2. Copy project files to `/usr/local/cpanel/whm/addons/WHMCloudFlare/`
3. Set required permissions:
   ```bash
   chmod +x install/install.sh
   ./install/install.sh
   ```
4. Access settings via WHM > Plugins > WHMCloudFlare

## ⚙️ Configuration

1. Enter Cloudflare API Token or API Key + Email
2. Set Zone ID for the domain
3. Select automatic record types
4. Save settings

## 🔧 Requirements

- WHM/cPanel version 11.80 or higher
- PHP 7.4 or higher
- Access to Cloudflare API
- cURL extension

## 🌐 Multi-Language Support

The module supports both **Persian (Farsi)** and **English** languages. You can switch languages from the language selector in the header of any page.

## 📝 License

This project is released under the MIT License. For more details, see the [LICENSE](LICENSE) file.

## 🤝 Contributing

Contributions, issues, and pull requests are always welcome!

1. Fork the repository
2. Create a new branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to your branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📧 Contact

- **GitHub:** [@hosseinabdinasab](https://github.com/hosseinabdinasab)
- **Telegram:** [@HOSSEINABDINASAB](https://t.me/HOSSEINABDINASAB)
- **Website:** [DonyayeLink](https://donyayelink.click/aq9qc)

## ⭐ Stars

If this project was useful to you, please give it a ⭐!

## 📚 More Documentation

- [🚀 Quick Start Guide](QUICK_START_EN.md) | [راهنمای سریع (Persian)](QUICK_START.md)
- [📖 Full Installation Guide](INSTALL_EN.md) | [راهنمای نصب (Persian)](INSTALL.md)
- [✨ Features List](FEATURES.md)
- [📡 API Documentation](docs/API.md)
- [❓ FAQ](docs/FAQ.md)
- [🔧 Troubleshooting](docs/TROUBLESHOOTING.md)

