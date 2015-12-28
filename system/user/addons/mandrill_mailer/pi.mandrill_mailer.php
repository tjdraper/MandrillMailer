<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Mandrill_mailer {

	public function __construct()
	{
		// Fetch Parameters
		$this->tagContents = ee()->TMPL->tagdata;
		$this->formClass = ee()->TMPL->fetch_param('class');
		$this->formId = ee()->TMPL->fetch_param('id');
		$this->return = ee()->TMPL->fetch_param('return');
		$jsonReturn = ee()->TMPL->fetch_param('json');
		$this->jsonReturn = $jsonReturn == 'yes' ? true : false;
		$this->required = explode('|', ee()->TMPL->fetch_param('required'));
		$this->allowed = explode('|', ee()->TMPL->fetch_param('allowed'));
		$to = explode('|', ee()->TMPL->fetch_param('to'));
		$this->to = ! empty($to[0]) ? $to : false;
		$this->from = ee()->TMPL->fetch_param('from');
		$this->fromName = ee()->TMPL->fetch_param('from-name');
		$this->subject = ee()->TMPL->fetch_param('subject');
		$message = explode('|', ee()->TMPL->fetch_param('message'));
		$this->message = ! empty($message[0]) ? $message : false;
		$privateMessage = ee()->TMPL->fetch_param('private_message');
		$this->privateMessage = ($privateMessage == 'yes')? true : false;

		// Get form attr attributes
		$this->formAttr = array();

		foreach (ee()->TMPL->tagparams as $key => $val) {
			if (strncmp($key, 'attr:', 5) === 0) {
				$this->formAttr[substr($key, 5)] = $val;
			}
		}

		// If there was an error posting, fill in the form values from the post
		$this->variables = array();
		foreach ($this->allowed as $allowed) {
			$this->variables[0][$allowed] = ee()->input->post($allowed);
		}
	}

	public function form()
	{
		// Make sure allowed param is set
		if (empty($this->allowed[0])) {
			return;
		}

		// Detect whether this is a submission or not
		if ($_POST) {
			$returnData = $this->_postForm();
		} else {
			$returnData = $this->_setForm();
		}

		return $returnData;
	}

	private function _postForm()
	{
		// Check the form submission for errors
		$errors = $this->_checkForm();

		// Return errors if there are any
		if ($errors) {
			if ($this->jsonReturn === true) {
				$output = array(
					'success' => 0
				);

				foreach ($this->required as $required) {
					if (! empty($errors['error:' . $required])) {
						$output['errors'][] = $required;
					}
				}

				ee()->output->send_ajax_response($output);
			} else {
				return $errors;
			}
		}

		// Set up Mandrill
		require_once 'lib/Mandrill/src/Mandrill.php';
		$mandrill = new Mandrill(ee()->config->item('mandrill_key'));

		// Set up message and set the reply to as the sender
		$message = array(
			'important' => false,
			'track_opens' => false,
			'track_clicks' => false,
		);

		// Set the reply-to to the from email
		if (! empty($this->from)) {
			$message['headers']['Reply-To'] = $this->from;
		} else {
			$message['headers']['Reply-To'] = $this->post['from-email'];
		}

		// Set the "from" name if it exists
		if (! empty($this->fromName)) {
			$message['from_name'] = $this->fromName;
		} else if (ee()->input->post('from-name')) {
			$message['from_name'] = $this->post['from-name'];
		}

		// Set the "to" email
		if (! empty($this->to)) {
			foreach ($this->to as $key => $email) {
				$message['to'][$key]['type'] = 'to';
				$message['to'][$key]['email'] = $email;
			}
		} else {
			$message['to'][0]['email'] = $this->post['to-email'];

			// Set the "to" name if it exists
			if (ee()->input->post('to-name')) {
				$message['to'][0]['name'] = $this->post['to-name'];
			}
		}

		// Set the subject
		if (! empty($this->subject)) {
			$message['subject'] = $this->subject;
		} else {
			$message['subject'] = addslashes($this->post['subject']);
		}

		// Set the from email to the webmaster email for best deliverability
		$message['from_email'] = ee()->config->item('webmaster_email');

		// Disable logging if private message has been set
		if ($this->privateMessage) {
			$message['view_content_link'] = false;
		}

		// Content

		// If message parameter is not specified, populate the array with all
		// post values
		if ($this->message === false) {
			foreach ($this->post as $key => $value) {
				$this->message[] = $key;
			}
		}

		$htmlContent = '';
		$textContent = '';

		foreach ($this->post as $key => $value) {
			if (in_array($key, $this->message)) {
				$key = str_replace('-', ' ', $key);
				$key = ucwords($key);

				$value = $value . '

';

				$htmlContent .= '<strong>' . $key . '</strong>: ';
				$htmlContent .= nl2br(htmlentities($value, ENT_QUOTES));

				$textContent .= $key . ': ';
				$textContent .= addslashes($value);
			}
		}

		// Set the content to the $message array
		$message['html'] = $htmlContent;
		$message['text'] = $textContent;

		// Send the message
		try {
			$async = true;

			$result = $mandrill->messages->send($message, $async);

			$mandrillSuccess = true;
		} catch(Mandrill_Error $e) {
			$mandrillSuccess = false;
		}

		// Set up the appropriate return
		if (! empty($this->return)) {
			// Redirect to the return paramter
			if ($mandrillSuccess) {
				ee()->functions->redirect($this->return);
			} else {
				// Set the form up
				$form = $this->_setForm();

				// Return with appropriate variables
				return $form;
			}
		} elseif ($this->jsonReturn == true) {
			$output = array(
				'success' => ($mandrillSuccess == true) ? 1 : 0
			);

			ee()->output->send_ajax_response($output);
		} else {
			// Set the succes variable
			$this->variables[0]['success'] = $mandrillSuccess;

			// Clear variables on success so form doesn't repopulate
			if ($mandrillSuccess) {
				foreach ($this->allowed as $allowed) {
					$this->variables[0][$allowed] = false;
				}
			}

			// Return the form
			return $this->_setForm();
		}
	}

	private function _checkForm()
	{
		// Make sure we have from and to email addresses and a subject

		if ($this->to === false AND ! in_array('to-email', $this->required)) {
			$this->required[] = 'to-email';
		}

		if ($this->from === false AND ! in_array('from-email', $this->required)) {
			$this->required[] = 'from-email';
		}

		if ($this->subject === false AND ! in_array('subject', $this->required)) {
			$this->required[] = 'subject';
		}

		// Initially set errors to false
		$errors = false;
		$notAllowed = array();

		// Check that all required fields are present
		if (! empty($this->required[0])) {
			foreach ($this->required as $required) {
				$thisContent = ee()->input->post($required);

				if (empty($thisContent)) {
					$this->variables[0]['error:' . $required] = true;
					$errors = true;
				}
			}
		}

		// Check that only allowed fields are part of the submission

		$this->post = array();

		foreach ($_POST as $postKey => $postValue) {
			if ($postKey !== 'submission') {
				$key = ee()->security->xss_clean($postKey);
				$value = ee()->security->xss_clean($postValue);
				$this->post[$key] = $value;
			}
		}

		foreach ($this->post as $postKey => $postValue) {
			if (! in_array($postKey, $this->allowed)) {
				$notAllowed[] = true;
			}
		}

		if (! empty($notAllowed)) {
			show_error('Your submission contains disallowed inputs');
			exit();
		}

		// If there are errors, set the form and return
		if ($errors) {
			$this->variables[0]['error'] = true;
			return ($this->jsonReturn) ? $this->variables[0] : $this->_setForm();
		}

		// If there are no errors, just return
		return false;
	}

	private function _setForm($parse = true)
	{
		ee()->load->helper('form');

		$attributes = array();

		if ($this->formClass) {
			$attributes['class'] = $this->formClass;
		}

		if ($this->formId) {
			$attributes['id'] = $this->formId;
		}

		if ($this->formAttr) {
			foreach ($this->formAttr as $key => $val) {
				$attributes[$key] = $val;
			}
		}

		$siteUrl = rtrim(ee()->config->item('site_url'), '/');
		$siteIndex = ee()->config->item('site_index');
		$siteIndex = $siteIndex ? $siteIndex . '/' : $siteIndex;
		$uriString = ee()->uri->uri_string;

		$form = form_open(
			"{$siteUrl}/{$siteIndex}{$uriString}",
			$attributes
		) . $this->tagContents . '</form>';

		return ee()->TMPL->parse_variables($form, $this->variables);
	}
}
