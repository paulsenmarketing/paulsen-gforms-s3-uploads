# Paulsen GForms S3 Uploads

This plugin moves files submitted through Gravity Forms to S3 after form submission. It then alters the value displayed when viewing entries to show the S3 URL

## Installation 

1. Define the some constants in wp-config.php:

   `define( 'GFORM_S3_FORM_ID', <Form id> );`

   `define( 'GFORM_S3_FIELD_ID', <field id> );`

   `define( 'GFORM_S3_BUCKET', '<bucket name>' );`

   `define( 'AWS_ACCESS_KEY_ID', '<aws access key>' );`

   `define( 'AWS_SECRET_ACCESS_KEY', '<aws secret key>' );`

2. Install and activate like a typical WP plugin.

## To Do

- Support multiple forms and fields
- Add admin area to select which forms and fields to use
- Add option to apply to any upload field
- Integrate with the wp-amazon-web-services plugin
- Verify that the file was successfully uploaded
- Modify the actual field value after uploading to S3 rather than using a filter to modify the output