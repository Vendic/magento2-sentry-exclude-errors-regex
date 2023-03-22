## Exclude errors from Sentry using regex

With this module we can exclude errors from sentry using regex matching. 

### Installation
```bash
composer require vendic/magento2-sentry-exclude-errors-regex
```

### Usage
Add entry to the env.php within the sentry node sentry. Example:
```
    'sentry' => [
        'ignore_exceptions_regex' => [
            '^Placing an order with quote_id \\w{32} is failed: The payment is REFUSED\\.$'
        ]
    ]
```
