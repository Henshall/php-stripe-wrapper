# Stripe Php Wrapper
## Version: 1.0.0

This is a wrapper for the Stripe PHP Package 'stripe/stripe-php'.

I wanted to created this package to help simplify payment processing with
Stripe - and help developers quickly start using stripe with lots of examples.
Although this package is mostly a simple wrapper, it has a unique way of handling errors (important),
processing web-hooks, works with stripe connect, and has documentation to provide
lots of examples on how to use it.

## Installation with Composer:
```bash
composer require henshall\stripe_wrapper
```

## Pre-requisites
- Create a Stripe account (https://stripe.com/)
- Get your Public and Secret key's in the developers section. 

## Usage:

To process payments with stripe we need two things, to collect the credit card (and other) information from the customers on a front end HTML form, and then to process this information and send it to stripe on the back end.
I have divided the usage section into 1) front end examples and 2) back end examples. You need both to process payments.


## Front End:

Create a form on your HTML page like so. Here you will capture customer information for later use.
Make sure to include your public stripe key. in the data-key section, and the data-amount should be in 
cents (1/100) of the value of your currency. ex. 1000 USD = $10.

### Front End Example 1:
Use this to create a simple pop-up form.
```html
<form class="" action="/pay" method="post">
    <input type="hidden" name="_token" value="insert_csrf_token_here">
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


### Front End Example 2:
Use this to create an inline form with bootstrap
```html
<body>
    <div class="container">
        <div class="row mt-5">
            <div class="col-12 col-sm-12 col-md-6 offset-md-3">
                <form action="/pay" method="post" id="payment-form">
                    <input type="hidden" name="_token" value="insert_csrf_token_here">
                    <div class="form-row">
                        <div id="card-element" class="mt-3">
                            <!-- A Stripe Element will be inserted here. -->
                        </div>
                        <!-- Used to display form errors. -->
                        <div id="card-errors" role="alert"></div>
                    </div>
                    <button class="btn btn-primary mt-3">Submit Payment</button>
                    <br>
                    <img src="images/stripelogo.png" alt="" class="image mt-3">
                </form>
            </div>
        </div>
    </div>
    
    <style media="screen">
    /**
    * The CSS shown here will not be introduced in the Quickstart guide, but shows
    * how you can use CSS to style your Element's container.
    */
    #card-element{
        width:100%;
        border-style: solid;
        border-color: #b5b5b5;
        border-width: 1px;
    }
    .image{
        max-width: 122px;
    }
    .StripeElement {
        box-sizing: border-box;
        height: 40px;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 4px;
        background-color: white;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }
    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }
    .StripeElement--invalid {
        border-color: #fa755a;
    }
    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
    .form-row{
        margin: 0px;
    }
    </style>
    <script src="https://js.stripe.com/v3/"></script>
    
    <script type="text/javascript">
    var stripe_token = "stripe_token_goes_here";
    // Create a Stripe client.
    var stripe = Stripe(stripe_token);
    // Create an instance of Elements.
    var elements = stripe.elements();
    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    // Create an instance of the card Element.
    var card = elements.create('card', {style: style});
    // Add an instance of the card Element into the `card-element` <div>.
    card.mount('#card-element');
    // Handle real-time validation errors from the card Element.
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    // Handle form submission.
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });
    });
    // Submit the form with the token ID.
    function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.getElementById('payment-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);
        
        // Submit the form
        form.submit();
    }
    </script>                
</body>
```


# Back End:
Back End Notes: you can see in the examples that the form will send a post request to /pay. 
Before it hits this location, it will send a request to stripe servers
with the information and return a token (["stripeToken"]). We can use this token on the back end
to process events like customer creation and payments. 

Please see below for some example of what you can do with the token.

### Charge an anonymous person (one time charge). 
Use this to charge customers when you don't need to collect information such as their address, or instructions regarding the product/service.
```php
$sw = new StripeWrapper;
$sw->setApiKey("sk_test_kalsdjfsdkfjasdfjkasjdfjs");
$sw->anonymousOneTimeCharge(['amount' => 1000, 'currency' => "USD", 'description' => "Payment for xyz service or product", "source" => $_POST["stripeToken"]]);
if ($sw->error) {
// Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
}
```

### Create a customer and then charge that customer (one time charge)
Use this when you want to collect information about a customer. We will create a 
customer object in stripe, and then charge the customer after.
```php
$sw = new StripeWrapper;
$sw->setApiKey("sk_test_Gsdfsdfsdfsdfsdfdsfsdfsdfsdfsdf");
$customer = $sw->createCustomer(["name" => "testing dude", "email" => "test@test.com", "description" => "im a real person", "source" => $_POST["stripeToken"]]);
$sw->chargeCustomer(['amount' => 1000, 'currency' => "USD", 'description' => "Payment for xyz service or product", 'customer' => $customer]);
if ($sw->error) {
// Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
}
```


### Charge an existing customer (one time charge).
Use this when you already have a customer - ei, you want to charge an existing customer
with one one time fee. Here we need to retrieve a customer from stripe.
```php
$sw = new StripeWrapper;
// set secret key and pass to stripe.
$sw->setApiKey("sk_test_sdfkasdjsdfsdfsdfsdffdfsd");
//pass the customer id from stripe to get the customer object
$customer = $sw->retrieveCustomer("cus_GPeOHGPqGH1fdd");
//create charge
$sw->chargeCustomer(['amount' => 1000, 'currency' => "USD", 'description' => "Payment for xyz service or product", 'customer_id' => $customer]);
if ($sw->error) {
// Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
}
```


### Create Subscriptions and charge new customers on a recurring basis. (subscription)
To create subscriptions with stripe, we first need to create a customer in stripe as well as a plan.
We need both to create a subscription. Its possible to use existing customers and plans you have previously created
but here we will create a new plan, and a new customer, and use them to create a subscription. 

#### 1) Create a plan 
Option A) Login into Strip and create a plan manually under the billing/products tab.

Option B) Run the following code only one time to create a plan we will continue to use the plan for all future subscriptions. 
```php
$sw = new StripeWrapper;
$sw->setApiKey("sk_test_Gc4sdfgsdfhghsdfghsdjjjjhggg");
// (Note: do not run this code with every subscription - we only need to create a plan one time.)
// Make sure the amount is in cents
// Currency types suppoted found here: https://stripe.com/docs/currencies  (Ex. USD, EUR, CAD, Etc.)
// Interval types include: day, week, month, and year
// Product name can be anything, we will use this name when charging a plan later.
// Note: only run once - do not create a new plan for every charge or customer.
$sw->createPlan(['id' => "40_dollar_monthly_subscription", 'amount' => 4000, 'currency' => "NZD", 'interval' => "month", 'product' => ['name' => 'subscriptions']]);
if ($sw->error) {
    // Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
}
```

#### 2) Create subscription
Once the plan is set up, we can create a customer and use them to create the subscription.
```php
$sw = new StripeWrapper;
$sw->setApiKey("sk_test_Gsdfsdahfjshadfjhsadfjh");
$plan = $sw->retrievePlan("40_dollar_monthly_subscription");        
$customer = $sw->createCustomer(["name" => "testing dude", "email" => "test@test.com", "description" => "im a real person", "source" => $_POST["stripeToken"]]);
$sw->createSubscription($customer, $plan);
if ($sw->error) {
// Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
}
```




### Create Subscriptions and charge existing customers on a recurring basis. (subscription)
Here we will assume that you have already have a customer and a plan set up.
We will use them to create a subscription. Use this if you need to charge an existing customer.
```php
$sw = new StripeWrapper;
$sw->setApiKey("sk_test_Gsdfsdahfjshadfjhsadfjh");
$customer = $sw->retrieveCustomer("cus_GPeOHGPqGH1Qhc");
$plan = $sw->retrievePlan("40_dollar_monthly_subscription");
$sw->createSubscription($customer, $plan);
if ($sw->error) {
    // Where to put your logic if there is an error. (Save error to DB, or log file, or email to yourself etc.)
    die($sw->error);
}
```






