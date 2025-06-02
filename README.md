# Social Media Website

A modern, responsive social media platform built with PHP, MySQL, and JavaScript.

## Features
- User registration and authentication
- Create posts with text and images
- Like and comment on posts
- Friend system
- Direct messaging
- Location tagging on posts
- User profiles
- Block and report users
- Admin panel for user management
- Responsive design

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- GD Library for image processing

## Installation

1. Clone the repository to your web server directory
2. Create a MySQL database named `social_media`
3. Import the `database.sql` file into your database
4. Update database credentials in `includes/config.php`
5. Create the following directories and ensure they are writable:
   - `uploads/` (for post images)
   - `images/` (for profile pictures)
6. Place a default avatar image named `default.jpg` in the `images/` directory

## Default Admin Account
- Username: admin
- Password: admin123
**Important:** Change the admin password immediately after first login!

## Security Notes
- Always use HTTPS in production
- Keep PHP and MySQL updated
- Regularly backup your database
- Monitor the admin reports section for suspicious activity

## File Structure
