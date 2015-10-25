# Version 2.0.0

## Released 10-25-2015

### New

- Added support for ExpressionEngine 3.

### Breaking

- This release is not compatible with ExpressionEngine 2.x

### Updated

- Updated Manrill PHP API from 1.0.53 to 1.0.55

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