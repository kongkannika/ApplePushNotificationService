# ApplePushNotificationService
Small PHP Script of Apple Push Notification Service

## Example
```php
	<?php
	  	include_once 'ApplePushNotificationService.php';
	  	$apns = new ApplePushNotificationService('/path/to/certificate.pem', ApnsParams::ENVIRONMENT_PRODUCTION);
	  	$apns->setMessage('You have received a message from me');
	  	$apns->setCustomMessage(array(
	  		'id' => 1000,
	  		'type' => 'webview',
	  		'url' => 'http://www.google.com',
	  		// ...
	  	));
	  	$deviceTokens = array(
	  		'B62F1B80A389AB6138F9BF2BFF5F4D5B1E8BB8E8D206399834FF06376EE2E84B',
	  		'AFCE92535085CFFFE4B8588F8D5A6575878981A2C7D2E88395219536585A02D5',
	  		// ...
	  	);
	  	foreach ($deviceTokens as $token) {
	  		$apns->addToken($token);
	  	}
	  	$apns->send();
	?>
```
