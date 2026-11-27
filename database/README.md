# CampusNest Database Setup

## XAMPP / phpMyAdmin

1. Open `http://localhost/phpmyadmin`.
2. Import `database/campusnest.sql`.
3. Open `http://localhost/BINARYHQ/BINARYHQ/php/setup.php` and send a POST, or run the setup through PowerShell:

```powershell
Invoke-WebRequest -Method POST http://localhost/BINARYHQ/BINARYHQ/php/setup.php
```

## Default administrator

- Email: `admin@campusnest.cm`
- Password: `Admin123!`

Change this password before production use.

The application connection defaults to:

- Host: `localhost`
- Database: `campusnest`
- User: `root`
- Password: empty

Update `php/connect.php` before deploying outside XAMPP.

The housing workflow tables (`tenancies`, `payments`, `reviews`, `conversations`,
`messages`, `service_catalog`, and `listing_photos`) are included in
`campusnest.sql` and are also created automatically on the next application
request for existing installations. Landlords and admins can add up to 12
interior photo URLs per house; the public View dialog displays them as a gallery.
Rent and utility payments are recorded for landlord/admin confirmation; no bank or
mobile-money payment provider is currently configured.

## ASTRA services and student vacancies

CampusNest reads ASTRA's public service catalog and published student vacancies
through `php/astra.php`. The server-side proxy uses `ASTRA_BASE_URL`, which
defaults to `http://localhost/ASTRA`; set it to ASTRA's project root when the
apps are hosted elsewhere. ASTRA accounts, bookings, and request tracking remain
separate, so tenants sign in on ASTRA to place and manage service requests.
Student applications follow each vacancy's application instructions or HTTPS
link; they are not submitted as technician assignments.
