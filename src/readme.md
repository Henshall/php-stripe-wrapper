# Stripe Php Wrapper
## Version: 1.0.0

This is a wrapper for the Stripe PHP Package 'stripe/stripe-php'.

I wanted to created this package to help simplify payment processing with
Stripe - and help developers quickly start using stripe with lots of examples.
Although this package is mostly a simple wrapper, it has a unique way of handling errors (important),
processing web-hooks, and working with Stripe Connect.

## Installation:
```bash
composer require henshall\stripe_wrapper
```

## Pre-requisites
- Create a Stripe account (https://stripe.com/)
- Get your Public and Secret key's in the developers section. 

## Usage:

Front End:
Create a form on your HTML page like so. Here you will capture customer information for later use.
Make sure to include your public stripe key. in the data-key section, and the data-amount should be in 
cents (1/100) of the value of your currency. ex. 1000 USD = $10.
```bash
<form class="" action="/pay" method="post">
            {{ csrf_field() }}
            <script
                    id="stripe_script"
                    src="https://checkout.stripe.com/checkout.js"
                    class="stripe-button"
                    data-key="pk_test_Esadfkjasjhdflkahjsfkajhs"
                    data-amount="1000"
                    data-name="Your App Name"
                    data-description=" Payment For : Some Product"
                    data-locale="auto"
                    data-currency="USD"
                    data-label="Purchase (Credit Card) $10">
                    </script>
        </form>
```



Back End Notes: you can see that the form above will send a post request to /pay. 
Before it hits this location, it will send a request to stripe servers
with the information and return a token (["stripe_token"]). We can use this token on the back end
to process events like customer creation and payments. 

Please see below for some example of what you can do with the token.

Create a customer and then charge the customer.
```bash

$sw = new StripeWrapper;
$sw->setApiKey("sk_test_sdfasdkfaskdjflaksjdflas");
$customer = $sw->createCustomer([
"name" => "testing_person", "email" => "test@test.com", "description" => "im a real person", 
"stripe_token" => $_POST["stripe_token"]]);

$sw->chargeCustomer([
'amount' => 1000, 'currency' => "USD", 'description' => "Payment for xyz service or product", 
'customer_id' => $customer->id]);
if ($sw->error) {
    // Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
}
```



