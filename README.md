# PolicyMaker

PolicyMaker is a lightweight PHP and SQLite web application for creating, managing, publishing, and displaying privacy policies for Android mobile applications.

It includes an administrator-only Privacy Policy wizard that generates structured policy text from simple yes/no, radio, checkbox, and text-field inputs. Each generated policy receives a permanent `policy_id` that can be activated and displayed publicly.

## Features

- Password-protected administrator dashboard.
- Installation script that creates the SQLite database and generates the first administrator password.
- Administrator profile fields for author name, organization, contact email, and website.
- Privacy Policy wizard for Android apps with questions for:
  - application name, package name, website, and effective date
  - personal information collection
  - account registration
  - analytics and crash reporting
  - advertising
  - location, camera, microphone, and contacts access
  - Android permissions
  - service providers
  - payments and in-app purchases
  - data retention
  - security measures
  - user privacy rights
  - children's privacy
- Data retention presets and checkboxes for common policy components.
- Children's Privacy options, including simple offline apps that do not collect children's information.
- Service Providers options, including fully offline apps with no third-party data sharing.
- Generated policy list with app name, policy ID, status, style, and update time.
- Activate, deactivate, edit, and delete generated policies.
- Public policy display by URL, for example:

```text
http://localhost/policymaker/view.php?policy_id=34
```

- Public privacy policy pages include schema.org JSON-LD metadata.
- Three to five simple policy display styles.
- Bootstrap and Font Awesome based interface.
- Apache `.htaccess` and Nginx rewrite samples for prettier public policy URLs.

## Installation

1. Copy the project folder to your web root, for example:

```text
C:\xampp74\htdocs\policymaker
```

2. Make sure PHP has SQLite/PDO SQLite enabled.
3. Visit the installer in your browser:

```text
http://localhost/policymaker/install.php
```

4. Fill in the initial administrator profile fields.
5. Click install. The installer creates the SQLite database and generates the first administrator password.
6. Save the generated password immediately, then log in at:

```text
http://localhost/policymaker/login.php
```

## Pretty URLs

The default public policy URL is:

```text
http://localhost/policymaker/view.php?policy_id=34
```

Apache rewrite examples are included in `.htaccess.sample`, and Nginx examples are included in `nginx-policy-rewrite.sample`.

Example pretty URLs:

```text
http://localhost/policymaker/policy/34
http://localhost/policymaker/privacy-policy/34
```

## Project URL

GitHub: https://github.com/mypapit/policymaker


## License

Copyright (c) 2026 Mohammad Hafiz bin Ismail.

PolicyMaker is licensed under the Simple 2-Clause BSD license. See `LICENSE` for details.
