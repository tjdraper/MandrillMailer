# Mandrill Mailer 1.0.0 for ExpressionEngine

Easily set up an email form to send via the Mandrill API.

## Purpose

An easy and uncomplicated way to set up a form submission in an ExpressionEngine template and mail it via Mandrill. All you'll need is a mandrill account from which to supply this add-on an API key.

## Settings

You’ll want to have your webmaster email set up in ExpressioneEngine. By default that is the address emails will be sent with. The reply-to header will be set as you specify, but the actual from address should be a trusted address. You should have this set up as a sending address in your Mandrill account.

Mandrill Mailer also requires the API key to be set up in the config file like so:

	$config['mandrill_key'] = 'XXXXXXXXXXXX';

If your email settings are in the config file and you've already set up ExpressionEngine to use Mandrill you might want to do something like this so you don’t have to enter your Mandrill API key in more than one place:

	$config['mandrill_key'] = $config['smtp_password'];

## Tag Pair

The tag pair lets you put pretty much anything you want to between it. It will output the opening and closing form tags. It will loop through all your inputs and put them in the email.

### Required Parameters

	allowed="phone|message"

Required. A pipe delimited list of fields that are allowed to be submitted to this form.

### Optional Parameters

	required="phone|message"

Optional. A pipe delimited list of fields that are required for this form.

	message="phone|message"

Optional. Specify the fields to include in the message body. By default, if this parameter is not specified, all fields are included. But you may find you want to include a to and/or from email or some other automatically processed fields that you don't want in the body of the email. In that case, use this parameter to explicitly specify which fields to include in the message.

	return="/my/uri"

Optional. Specifies a URI path to redirect to.

	json="yes"

Optional. Output results as a simple JSON object rather than returning HTML. Great for AJAX submissions.

On success, the object will contain `"success": 1`.

If submission was unsuccessful, the object will contain `"success": 0`, and if there are errors related to fields in the submission, they will be listed in the errors array as a list of field names.

	{
		"success": 0,
		"errors": ["field-name","another-field","more-fields"]
	}

&nbsp;

	class="my-class"

Optional. Specifies a class to your `<form>` tag.

	id="my-id"

	attr:my-attribute="my-data"

Optional. Specifies any arbitrary parameters you would like to add to your `<form>` tag. Useful specifically for data attributes.

Optional. Specifies an id to your `<form>` tag.

	to="janedoe@internet.com"

Conditionally Optional. A pipe delimited list of email addresses. This is required if you do not have an input with a name of `to-email`.

	from="johndoe@internet.com"

Conditionally Optional. An email address that this email is from. This will only be used in the reply-to header. This is required if you do not have an input with a name of `from-email`.

	from-name="John Doe"

Optional. If specifying the `from` parameter in the tag, you can also specify the `from-name` parameter. This can also be set from an input with the name of `from-name`.

	subject="Cool Stuff Happening"

Conditionally Optional. Specify a subject for the email. Required if you do not have an input with the name of `subject`.

### Tags

	{field-name}

Mandrill Mailer makes tags for all of your allowed fields available so that on post back, if there is an error, the content the user entered in will still be available. So you can do something like this:

	<input type="text" name="from-name" placeholder="Your Name" value="{from-name}">

`{from-name}` will contain the submission value of the field and so the field will be re-populated.

	{error:field-name}

If a required field is left blank and you are not returning JSON, you'll need to let the user know that they didn't fill in one of the required inputs. So you might do something like this:

	<input type="text" name="from-name" placeholder="Your Name" value="{from-name}">
	{if error:from-name}Oops, you need a name!{/if}

These variables are boolean values to check against.

	{if success}
		Yay!
	{if:elseif error}
		Your submission did not go through, please check your info.
	{/if}

Upon submission, the `success` or `error` variable is made available as a boolean to check against.

### Automatic Fields

Depending on the type of form you need, you may or may not wish to use the automatic fields available. These fields can be overridden by the tag parameters, but if the tag parameters are not present, it falls back to these fields.

	<input type="email" name="from-email">

If you wish to let the user specify a from email, give your input the name of `from-email`. This will automatically be set as the reply-to address.

	<input type="text" name="from-name">

If you wish to let the user specify their name, set your input name to `from-name`. This will be set as the from name on the email.

	<input type="email" name="to-email">

Let the user specify the email recipient. Set your input name to `to-email`. Useful for "email a friend" forms. This will be set as the to email.

	<input type="text" name="to-name">

Allow the user to specify the name of the recipient by setting this input name to `to-name`.

	<input type="text" name="subject">

Allow the user to specify the subject by setting the input name to `subject`.

## Example

So putting it all together, you might have a form that looks something like this:

	{exp:mandrill_mailer:form
		to="janedoe@internet.com"
		subject="Cool Stuff Happening"
		class="my-class"
		allowed="from-name|from-email|phone|content"
		required="from-name|from-email|content"}
		
		{if success}
			Yay!
		{if:elseif error}
			Your submission did not go through, please check your info.
		{/if}
		
		<input type="text" name="from-name" placeholder="Your Name" value="{from-name}" required>
		{if error:from-name}Oops, you need a name!{/if}
		
		<input type="email" name="from-email" placeholder="Email Address" value="{from-email}" required>
		{if error:from-email}Oops, you need a email address!{/if}
		
		<input type="text" name="phone" placeholder="Phone Number" value="{phone}">
		
		<textarea name="content" placeholder="Message" value="{message}></textarea>
		{if error:content}Oops, you need to write something!{/if}
		
		<input type="submit">
	{/exp:mandrill_mailer}

## License

Copyright 2014 TJ Draper, BuzzingPixel

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.