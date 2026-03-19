# Discord Webhook Portal

A modern web application for sending Discord webhook messages with a credit-based system.

## 🚀 Features

- ✅ Modern Blue/Black UI Design
- ✅ User Registration & Login System
- ✅ Credit System (90 credits per webhook)
- ✅ Passive Credit Earning (3 credits/minute)
- ✅ Discord Webhook Message Sender
- ✅ Text Editor with Formatting Tools
- ✅ Message Scheduling with Delay
- ✅ Loop Mode for Multiple Messages
- ✅ History Tracking
- ✅ Responsive Design

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Docker (optional)

## 🛠️ Installation

### Local Development

1. Clone the repository
```bash
git clone https://github.com/yourusername/discord-webhook-portal.git
cd discord-webhook-portal
```



## 🚀 Quick Deploy to Railway

1. **Create GitHub repository** with above structure
2. **Push code** to GitHub
3. **Go to Railway.app** → New Project → Deploy from GitHub
4. **Select your repository**
5. **Add MySQL** database service
6. **Add volume** to MySQL (mount: `/var/lib/mysql`)
7. **Set environment variables** (auto-linked)
8. **Deploy!**

## ✅ Verification Checklist

- [ ] All files are in correct folders
- [ ] No sensitive data in code (passwords, keys)
- [ ] `.gitignore` properly configured
- [ ] `composer.json` has correct dependencies
- [ ] Docker files are working locally
- [ ] README has complete instructions
- [ ] License file included
- [ ] Railway config files present

Yeh complete structure GitHub par upload karne ke liye tayyar hai. Aap is structure ke mutabiq saari files bana kar GitHub par push kar sakte hain.


```
discord-webhook-portal/
│
├── .github/
│   └── workflows/
│       └── deploy.yml              # GitHub Actions for auto-deploy
│
├── assets/
│   ├── css/
│   │   └── style.css                # Modern blue/black styling
│   │
│   ├── js/
│   │   └── main.js                   # Frontend functionality
│   │
│   └── images/
│       └── favicon.ico               # Optional: site icon
│
├── includes/
│   ├── config.php                     # Configuration file
│   ├── db.php                         # Database connection
│   ├── functions.php                   # Core functions
│   └── auth.php                        # Authentication checks
│
├── pages/
│   ├── login.php                       # Login page
│   ├── register.php                    # Registration page
│   ├── dashboard.php                    # User dashboard
│   ├── webhook-tool.php                  # Main webhook tool
│   └── logout.php                        # Logout handler
│
├── api/
│   ├── earn-credits.php                  # Credit earning API
│   ├── earn-credits-cron.php              # Cron job for credits
│   ├── send-webhook.php                    # Webhook sender API
│   ├── stop-loop.php                        # Stop loop API
│   └── get-history.php                       # History API
│
├── database/
│   └── schema.sql                          # Database schema
│
├── docker/
│   ├── Dockerfile                           # Docker configuration
│   ├── nginx.conf                            # Nginx config
│   ├── start.sh                               # Startup script
│   └── php.ini                                 # PHP configuration
│
├── .env.example                                # Environment variables example
├── .gitignore                                   # Git ignore file
├── .railway/config.json                          # Railway config
├── railway.toml                                   # Railway deployment config
├── README.md                                       # Project documentation
├── LICENSE                                         # MIT License
├── composer.json                                    # PHP dependencies
└── index.php                                         # Entry point
```
