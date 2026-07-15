# FarmNet Kenya

FarmNet Kenya is a web-based agricultural support platform that connects Kenyan farmers with verified agronomists. It brings professional advice, service requests, messaging, weather information, digital payments, and farm support tools into one system.

> This was developed as a final-year project. External integrations such as M-Pesa should use sandbox credentials during testing.

## Main Features

### Farmers

- Create and manage a user profile
- Find verified agronomists and view their services
- Send and track service requests
- Chat with agronomists in real time
- Exchange files and view message-seen indicators
- Make test payments through M-Pesa STK Push
- Rate and review agronomists
- View weekly weather information
- Ask agricultural questions through the Farmbot assistant

### Agronomists

- Apply for agronomist verification
- View verification status
- Create and manage professional services
- Receive and handle farmer service requests
- Communicate with farmers through chat
- Share files and manage availability
- Receive ratings and reviews

### Administrators

- Review and verify agronomist applications
- View and manage users
- Suspend accounts when necessary
- Review reports and contact submissions
- Search for users and inspect profiles
- Monitor activity through the administration dashboard

## Technology Stack

| Area | Technology |
| --- | --- |
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML, CSS and JavaScript |
| Asynchronous actions | AJAX |
| Local environment | Apache and MySQL through XAMPP |
| Payments | Safaricom M-Pesa Daraja sandbox |
| Additional services | Weather integration and Farmbot dataset |

## How the Platform Works

1. A user registers and selects the appropriate role.
2. An agronomist submits professional information for administrator verification.
3. Once verified, the agronomist can publish services.
4. A farmer discovers an agronomist and submits a service request.
5. The farmer and agronomist communicate through the built-in chat.
6. The farmer can complete a sandbox payment and later submit a rating or review.

## Local Installation

### Requirements

- XAMPP or another Apache, PHP and MySQL environment
- A modern web browser
- Git
- Internet access for external weather and M-Pesa services

### Setup

1. Clone the repository into your XAMPP web directory:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs
git clone https://github.com/AdnaanOsman1822/FARMNET-KENYA.git mychatui
cd mychatui
```

On Windows, the usual XAMPP web directory is `C:\\xampp\\htdocs`.

2. Start Apache and MySQL from the XAMPP control panel.

3. Create a MySQL database and import the SQL schema or database export supplied with the project.

4. Update the project's database configuration with your local database name, username, password and host.

5. Add your own sandbox or development credentials for external services. Never commit API keys, passwords, access tokens or production credentials to GitHub.

6. Open the application:

```text
http://localhost/mychatui/
```

## Main Data Areas

The platform manages information for:

- Users and roles
- Agronomist profiles and verification
- Services and service requests
- Chat messages and attachments
- Payment requests
- Ratings and reviews
- User reports and contact submissions

## Project Structure

Key areas of the repository include:

- `index.php` — role-based dashboard
- `includes/` — shared PHP logic, weather and chatbot functionality
- `Classes/` — application classes
- `ui/` — interface components and pages
- Authentication, chat, service, payment and administration pages

## Deployment and Security Notes

Before deploying publicly:

- Disable PHP error output and remove debug logging
- Store secrets outside the public repository, preferably in environment variables
- Use production database credentials with limited permissions
- Validate uploads and restrict allowed file types and sizes
- Enforce HTTPS and secure session cookies
- Replace all M-Pesa sandbox credentials and URLs with approved production settings
- Back up the database and test authentication, authorization and payment callbacks

## Project Status

The core FarmNet Kenya workflow has been completed and demonstrated. Further work should focus on production hardening, broader testing, deployment configuration, and monitoring.

## Author

Developed by [Adnaan Osman](https://github.com/AdnaanOsman1822).
