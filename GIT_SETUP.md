# راهنمای راه‌اندازی Git و Push به GitHub

این راهنما به شما کمک می‌کند پروژه WHMCloudFlare را در GitHub خود قرار دهید.

## مراحل

### 1. ایجاد Repository در GitHub

1. به [GitHub](https://github.com) بروید و وارد شوید
2. روی دکمه **"+"** در گوشه بالا راست کلیک کنید
3. **"New repository"** را انتخاب کنید
4. نام Repository را `WHMCloudFlare` قرار دهید
5. توضیحات را وارد کنید: `Automated DNS Record Management for WHM/cPanel and Cloudflare`
6. Repository را **Public** یا **Private** انتخاب کنید
7. **"Create repository"** را کلیک کنید

### 2. راه‌اندازی Git در پروژه محلی

```bash
# رفتن به دایرکتوری پروژه
cd /path/to/WHMCloudFlare

# مقداردهی اولیه Git
git init

# اضافه کردن تمام فایل‌ها
git add .

# Commit اولیه
git commit -m "Initial commit: WHMCloudFlare v1.0.0"

# اضافه کردن Remote Repository
git remote add origin https://github.com/hosseinabdinasab/WHMCloudFlare.git

# Push به GitHub
git branch -M main
git push -u origin main
```

### 3. دستورات Git مفید

#### مشاهده وضعیت
```bash
git status
```

#### اضافه کردن فایل‌های جدید
```bash
git add .
# یا برای فایل خاص
git add filename.php
```

#### Commit تغییرات
```bash
git commit -m "توضیح تغییرات"
```

#### Push به GitHub
```bash
git push origin main
```

#### Pull از GitHub
```bash
git pull origin main
```

#### مشاهده تاریخچه
```bash
git log
```

### 4. ساختار Branch پیشنهادی

```bash
# Branch اصلی
main

# Branch برای ویژگی‌های جدید
feature/new-feature

# Branch برای رفع باگ
bugfix/fix-name

# Branch برای مستندات
docs/update-readme
```

### 5. ایجاد Release

برای ایجاد Release در GitHub:

1. به Repository بروید
2. روی **"Releases"** کلیک کنید
3. **"Create a new release"** را انتخاب کنید
4. Tag version را وارد کنید (مثلاً `v1.0.0`)
5. Title و Description را پر کنید
6. **"Publish release"** را کلیک کنید

### 6. نکات مهم

⚠️ **هشدار:** فایل‌های زیر نباید در Git commit شوند:
- `config/settings.json` (حاوی API Token/Key)
- `config/.encryption_key` (کلید رمزنگاری)
- `logs/*.log` (لاگ‌ها)
- `cache/*` (فایل‌های Cache)

این فایل‌ها در `.gitignore` قرار دارند و به صورت خودکار نادیده گرفته می‌شوند.

### 7. Clone پروژه

برای Clone کردن پروژه در سرور دیگر:

```bash
git clone https://github.com/hosseinabdinasab/WHMCloudFlare.git
cd WHMCloudFlare
```

### 8. به‌روزرسانی پروژه

برای به‌روزرسانی پروژه از GitHub:

```bash
git pull origin main
```

## مشکلات رایج

### خطای Authentication

اگر خطای Authentication دریافت کردید:

```bash
# استفاده از Personal Access Token
git remote set-url origin https://YOUR_TOKEN@github.com/hosseinabdinasab/WHMCloudFlare.git
```

برای ایجاد Personal Access Token:
1. GitHub > Settings > Developer settings > Personal access tokens
2. Generate new token
3. دسترسی‌های لازم را انتخاب کنید

### خطای Permission Denied

مطمئن شوید که:
- به Repository دسترسی دارید
- SSH Key یا Personal Access Token تنظیم شده است

## منابع

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [GitHub CLI](https://cli.github.com/)

---

**نویسنده:** [Hossein Abdinasab](https://github.com/hosseinabdinasab)

