# Event Management System

This is a web-based Event Management System for university/student events. It supports two user roles:

- **Student:** register, log in, browse events, RSVP for events, view active bookings/history, and manage profile details.
- **Admin:** log in, manage events, upload event images, view dashboard statistics, and review/cancel RSVP records.

The application is built with PHP, MySQL, HTML, CSS, Bootstrap, and runs locally on WAMP.

## Project Structure

Place the project folder exactly as provided under:

```text
C:\wamp64\www\Final Web-based Programming Exam
```

Main files include:

- `login.php` - shared login page for students and admins
- `Register.php` - student registration
- `StudentDashboard.php` - student event browsing dashboard
- `StudentBookingandHistory.php` - student bookings and history
- `Account.php` - student profile management
- `adminDashboard.php` - admin dashboard
- `addEvent.php`, `editEvent.php`, `deleteEvent.php` - admin event management
- `viewRsvp.php` - admin RSVP records
- `project_db.sql` - database dump

## Setup Instructions

1. Install and start WAMP Server.

2. Copy this project folder into:

```text
C:\wamp64\www\
```

3. Open phpMyAdmin or MySQL command line and import the database dump:

```sql
SOURCE C:/wamp64/www/Final Web-based Programming Exam/project_db.sql;
```

Alternatively, in phpMyAdmin:

- Open `http://localhost/phpmyadmin`
- Go to **Import**
- Choose `project_db.sql`
- Click **Import**

4. Confirm database settings in `dbconnect.php`:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "event_mgmt_db";
```

5. Open the application in your browser:

```text
http://localhost/Final%20Web-based%20Programming%20Exam/login.php
```

## Default Login Credentials

### Admin

- Email: `admin@example.com`
- Password: `admin123`

### Student

- Email: `student@example.com`
- Password: `student123`

## Key Features

- Single login page for both admin and student users
- Role-based redirects after login
- 20-minute inactivity session timeout
- Student event browsing, filtering, RSVP, booking history, and profile editing
- Admin event creation, editing, deletion, image upload, dashboard statistics, and RSVP management
- Separate admin and student visual themes

## Notes

- The system expects the database name to be `event_mgmt_db`.
- Uploaded event images are stored in the `uploads` folder.
- If image uploads fail, confirm the `uploads` folder exists and is writable by WAMP.
