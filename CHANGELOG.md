# Version 1.1.0

## Released 05-29-2015

### New

- Added a new parameter to the tag pair, `private_message="yes"`, to disable message logging in Mandrill.

### Fixed

- Added a check to see if CSRF is disabled and put a hidden CSRF input on the form if necesary

# Version 1.0.0

## Released 03-05-2015

This is the initial release of Mandrill Mailer

### Features

- Easily set up forms and email them with the Mandrill API
- Receive a JSON response to an AJAX submission