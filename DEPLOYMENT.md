# 🚀 School Management System - Deployment Guide

This guide will help you deploy your School Management System to the web using the easiest methods available.

## 📋 Table of Contents
- [Prerequisites](#prerequisites)
- [Option 1: Railway Deployment (Recommended)](#option-1-railway-deployment-recommended)
- [Option 2: Render Deployment](#option-2-render-deployment)
- [Option 3: Heroku Deployment](#option-3-heroku-deployment)
- [Post-Deployment Setup](#post-deployment-setup)
- [Environment Variables](#environment-variables)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Prerequisites

1. **GitHub Account**: Your code should be in a GitHub repository
2. **Git installed** on your local machine
3. **Basic understanding** of web deployment concepts

---

## 🌟 Option 1: Railway Deployment (Recommended)

**Why Railway?** 
- ✅ Best for PHP/MySQL applications
- ✅ Generous free tier ($5/month credit)
- ✅ Automatic deployments from GitHub
- ✅ Built-in MySQL database
- ✅ Custom domains supported

### Step-by-Step Instructions:

#### 1. **Prepare Your Repository**

Ensure these files are in your repository:
- `railway.json` (already created)
- `Dockerfile.railway` (already created)
- Your existing `Dockerfile` and `docker-compose.yml`

#### 2. **Sign Up for Railway**

1. Go to [railway.app](https://railway.app)
2. Click **"Sign up"**
3. Sign up with your GitHub account
4. Verify your account

#### 3. **Create New Project**

1. Click **"New Project"**
2. Select **"Deploy from GitHub repo"**
3. Choose your school management system repository
4. Railway will automatically detect your Dockerfile

#### 4. **Add MySQL Database**

1. In your Railway project dashboard
2. Click **"+ New"** → **"Database"** → **"Add MySQL"**
3. Railway will automatically create a MySQL instance
4. Note down the connection details from the **"Connect"** tab

#### 5. **Configure Environment Variables**

In your Railway service settings, add these variables:

```bash
# Database Configuration (Railway will auto-populate these)
MYSQLHOST=containers-us-west-xxx.railway.app
MYSQLPORT=6543
MYSQLUSER=root
MYSQLPASSWORD=your-generated-password
MYSQLDATABASE=railway

# Application Configuration
RAILWAY_ENVIRONMENT=production
CI_ENV=production
```

#### 6. **Deploy**

1. Railway will automatically deploy when you push to your main branch
2. Your app will be available at a URL like: `https://yourapp.up.railway.app`
3. You can add a custom domain in the settings

#### 7. **Import Database**

1. Access your Railway MySQL database via the connection details
2. Import your `database_file/school.sql` file
3. You can use Railway's built-in database tools or a MySQL client

---

## 🎨 Option 2: Render Deployment

**Why Render?**
- ✅ Great for containerized applications
- ✅ Free tier available
- ✅ Automatic SSL certificates
- ✅ Good performance

### Step-by-Step Instructions:

#### 1. **Sign Up for Render**

1. Go to [render.com](https://render.com)
2. Sign up with your GitHub account

#### 2. **Create Web Service**

1. Click **"New +"** → **"Web Service"**
2. Connect your GitHub repository
3. Fill in the details:
   - **Name**: `school-management-system`
   - **Region**: Choose closest to your users
   - **Branch**: `main`
   - **Build Command**: Leave empty (using Docker)
   - **Start Command**: Leave empty (using Docker)

#### 3. **Configure Docker**

1. In **"Advanced"** settings:
   - **Dockerfile Path**: `Dockerfile`
   - **Docker Context**: Leave as root

#### 4. **Add PostgreSQL Database**

1. Create a new **PostgreSQL** database service
2. Note the connection details
3. You'll need to modify your database config for PostgreSQL

#### 5. **Environment Variables**

Add these in your Render service settings:

```bash
DATABASE_URL=postgresql://username:password@hostname:port/database
CI_ENV=production
```

---

## 🟣 Option 3: Heroku Deployment

**Note**: Heroku no longer offers a free tier (starts at $5/month)

### Step-by-Step Instructions:

#### 1. **Install Heroku CLI**

Download from [devcenter.heroku.com/articles/heroku-cli](https://devcenter.heroku.com/articles/heroku-cli)

#### 2. **Create heroku.yml**

Create this file in your repository root:

```yaml
build:
  docker:
    web: Dockerfile
run:
  web: apache2-foreground
```

#### 3. **Deploy**

```bash
# Login to Heroku
heroku login

# Create app
heroku create your-app-name

# Set stack to container
heroku stack:set container

# Add MySQL addon
heroku addons:create cleardb:ignite

# Deploy
git push heroku main
```

---

## 🔧 Post-Deployment Setup

### 1. **Database Setup**

After deployment, you need to:

1. **Import your database schema**:
   - Use the database connection details from your hosting provider
   - Import `database_file/school.sql`
   - You can use phpMyAdmin, MySQL Workbench, or command line

2. **Update base URL** (if needed):
   - Modify `application/config/config.php`
   - Set `$config['base_url']` to your deployed URL

### 2. **Test Your Application**

1. Visit your deployed URL
2. Try logging in with default credentials:
   - **Email**: `school@admin.com`
   - **Password**: `12345`
3. Test main functionalities

### 3. **Security Checklist**

- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Enable HTTPS (usually automatic on these platforms)
- [ ] Review and update security settings

---

## 🔧 Environment Variables Reference

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `MYSQLHOST` | Database hostname | `containers-us-west-xxx.railway.app` |
| `MYSQLPORT` | Database port | `6543` |
| `MYSQLUSER` | Database username | `root` |
| `MYSQLPASSWORD` | Database password | `your-password` |
| `MYSQLDATABASE` | Database name | `railway` |
| `CI_ENV` | CodeIgniter environment | `production` |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `BASE_URL` | Application base URL | Auto-detected |
| `TIMEZONE` | Application timezone | `Asia/Dhaka` |

---

## 🔍 Troubleshooting

### Common Issues:

#### 1. **Database Connection Errors**
- Verify environment variables are correctly set
- Check database service is running
- Ensure database exists and schema is imported

#### 2. **File Permission Errors**
- The Dockerfile should handle permissions automatically
- Check that `uploads/`, `application/cache/`, and `application/logs/` are writable

#### 3. **500 Internal Server Error**
- Check application logs in your hosting platform
- Verify `.htaccess` rules are working
- Ensure all PHP extensions are installed

#### 4. **Assets Not Loading**
- Check if your base URL is correctly configured
- Verify static files are being served properly

### Getting Help:

1. **Check Platform Logs**:
   - Railway: View logs in the deployment tab
   - Render: Check the service logs
   - Heroku: Use `heroku logs --tail`

2. **Database Issues**:
   - Test database connection using the provided credentials
   - Verify the database schema was imported correctly

3. **Application Issues**:
   - Enable CodeIgniter error logging
   - Check `application/logs/` for error messages

---

## 🎉 Success!

Once deployed successfully, your School Management System will be accessible to users worldwide. You can:

- Share the URL with teachers, students, and administrators
- Set up custom domains for a professional look
- Monitor usage and performance through your hosting platform's dashboard
- Scale resources as your user base grows

### Next Steps:

1. **Backup Strategy**: Set up regular database backups
2. **Monitoring**: Configure uptime monitoring
3. **Updates**: Plan for application updates and maintenance
4. **Security**: Regularly update dependencies and review security settings

---

## 📞 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review your hosting platform's documentation
3. Check CodeIgniter 3 documentation for framework-specific issues
4. Consider reaching out to the hosting platform's support team

Happy deploying! 🚀 