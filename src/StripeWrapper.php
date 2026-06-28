<?php

namespace Henshall;

class StripeWrapper
{
    public $error = NULL;
    public $secretKey = NULL;

    public function validateApiKey($key){
        if ($this->error) {return $this->error;}
        try {
            if (!$key) {
                throw new \Exception("apiKey does not exist", 1);
            }
            if (!is_string($key)) {
                throw new \Exception("apiKey is not a string", 1);
            }
            if (15 > strlen($key)) {
                throw new \Exception("apiKey is less than 15 characters", 1);
            }
            return $key;
        } catch (\Exception $e) {
            $this->error = "validateApiKey method failed: " . $e;
            return $this->error;
        }
    }

    public function setApiKey($key){
        if ($this->error) {return $this->error;}
        try {
            \Stripe\Stripe::setApiKey($key);
            $this->secretKey = $key;
            return $key;
        } catch (\Exception $e) {
            $this->error = "setApiKey method failed: " . $e;
            return $this->error;
        }
    }

    public function charge($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = "charge method failed: " . $e;
            return $this->error;
        }
    }

    public function createCustomer($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Customer::create($data);
        } catch (\Exception $e) {
            $this->error = "createCustomer method failed: " . $e;
            return $this->error;
        }
    }

    public function retrieveCustomer($customer_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Customer::retrieve($customer_id);
        } catch (\Exception $e) {
            $this->error = "retrieveCustomer method failed: " . $e;
            return $this->error;
        }
    }

    public function deleteCustomer($data){
        if ($this->error) {return $this->error;}
        try {
            if (gettype($data) == "string") {
                $customer = \Stripe\Customer::retrieve($data);
                $customer->delete();
            } elseif (gettype($data) == "object") {
                $data->delete();
            } else {
                throw new \Exception("deleteCustomer: cannot process type " . gettype($data), 1);
            }
        } catch (\Exception $e) {
            $this->error = "deleteCustomer method failed: " . $e;
            return $this->error;
        }
    }

    public function createPlan($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Plan::create($data);
        } catch (\Exception $e) {
            $this->error = "createPlan method failed: " . $e;
            return $this->error;
        }
    }

    public function createSubscription($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Subscription::create($data);
        } catch (\Exception $e) {
            $this->error = "createSubscription method failed: " . $e;
            return $this->error;
        }
    }

    public function retrieveSubscription($sub_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Subscription::retrieve($sub_id);
        } catch (\Exception $e) {
            $this->error = "retrieveSubscription failed: " . $e;
            return $this->error;
        }
    }

    public function cancelSubscription($sub){
        if ($this->error) {return $this->error;}
        try {
            $sub->cancel();
            return $sub;
        } catch (\Exception $e) {
            $this->error = "cancelSubscription method failed: " . $e;
            return $this->error;
        }
    }

    public function retrievePlans(){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Plan::all()["data"];
        } catch (\Exception $e) {
            $this->error = "retrievePlans method failed: " . $e;
            return $this->error;
        }
    }

    public function retrievePlan($plan_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Plan::retrieve($plan_id);
        } catch (\Exception $e) {
            $this->error = "retrievePlan method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Creates a Stripe Checkout Session for subscription plans.
     *
     * $data must include:
     *   - price_id       (string)  Stripe Price ID (e.g. price_xxx)
     *   - success_url    (string)  URL to redirect on success (append ?session_id={CHECKOUT_SESSION_ID})
     *   - cancel_url     (string)  URL to redirect on cancel
     *   - customer_email (string)  Pre-fill customer email (optional if customer set)
     *   - customer       (string)  Existing Stripe Customer ID (optional)
     *   - client_reference_id (string) Your internal user ID for webhook reconciliation
     *   - metadata       (array)   Optional extra metadata
     */
    public function createCheckoutSession($data){
        if ($this->error) {return $this->error;}
        try {
            $params = [
                'mode'        => 'subscription',
                'line_items'  => [[
                    'price'    => $data['price_id'],
                    'quantity' => 1,
                ]],
                'success_url' => $data['success_url'],
                'cancel_url'  => $data['cancel_url'],
            ];

            if (!empty($data['customer'])) {
                $params['customer'] = $data['customer'];
            } elseif (!empty($data['customer_email'])) {
                $params['customer_email'] = $data['customer_email'];
            }

            if (!empty($data['client_reference_id'])) {
                $params['client_reference_id'] = (string) $data['client_reference_id'];
            }

            if (!empty($data['metadata'])) {
                $params['metadata'] = $data['metadata'];
            }

            // Always create a customer record so we can use the billing portal later
            $params['customer_creation'] = empty($data['customer']) ? 'always' : null;
            if ($params['customer_creation'] === null) {
                unset($params['customer_creation']);
            }

            return \Stripe\Checkout\Session::create($params);
        } catch (\Exception $e) {
            $this->error = "createCheckoutSession method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Creates a Stripe Billing Portal Session so customers can manage their subscription.
     *
     * $customer_id  — Stripe Customer ID (cus_xxx)
     * $return_url   — URL to return to after the portal session
     */
    public function createBillingPortalSession($customer_id, $return_url){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\BillingPortal\Session::create([
                'customer'   => $customer_id,
                'return_url' => $return_url,
            ]);
        } catch (\Exception $e) {
            $this->error = "createBillingPortalSession method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Verifies and constructs a Stripe webhook event using the signing secret.
     * Returns the full \Stripe\Event object (not just data->object).
     *
     * $payload   — raw request body (file_get_contents('php://input'))
     * $sigHeader — value of the Stripe-Signature header
     * $secret    — webhook signing secret (whsec_xxx)
     */
    public function constructWebhookEvent($payload, $sigHeader, $secret){
        try {
            return \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->error = "Webhook signature verification failed: " . $e->getMessage();
            return $this->error;
        } catch (\Exception $e) {
            $this->error = "constructWebhookEvent method failed: " . $e;
            return $this->error;
        }
    }

    /** @deprecated Use constructWebhookEvent() for signature-verified webhooks */
    public function getWebhookInput($data){
        if (!$data || $data == NULL || $data == "") {
            $this->error = "getWebhookInput: data not passed correctly";
            return $this->error;
        }
        try {
            return json_decode($data)->data->object;
        } catch (\Exception $e) {
            $this->error = "getWebhookInput method failed: " . $e;
            return $this->error;
        }
    }

    public function retreiveAllWebhooks($data = null){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\WebhookEndpoint::all($data)["data"];
        } catch (\Exception $e) {
            $this->error = "retreiveAllWebhooks method failed: " . $e;
            return $this->error;
        }
    }

    public function createWebhook($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\WebhookEndpoint::create($data);
        } catch (\Exception $e) {
            $this->error = "createWebhook method failed: " . $e;
            return $this->error;
        }
    }

    public function retrieveWebhook($webhookId){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\WebhookEndpoint::retrieve($webhookId);
        } catch (\Exception $e) {
            $this->error = "retrieveWebhook method failed: " . $e;
            return $this->error;
        }
    }

    public function retrieveBalance(){
        if ($this->error) {return $this->error;}
        try {
            $stripeClient = new \Stripe\StripeClient($this->secretKey);
            return $stripeClient->balance->retrieve();
        } catch (\Exception $e) {
            $this->error = "retrieveBalance method failed: " . $e;
            return $this->error;
        }
    }

    // ── Stripe Connect ────────────────────────────────────────────────────────

    /**
     * Creates a Stripe Express Connect account for a restaurant.
     *
     * $data keys:
     *   - type         (string)  'express' (default)
     *   - country      (string)  ISO 3166-1 alpha-2, e.g. 'ES'
     *   - email        (string)  Pre-fill the account holder's email (optional)
     *   - metadata     (array)   Optional key/value metadata
     *   - capabilities (array)   Override default capabilities (optional)
     */
    public function createConnectAccount($data) {
        if ($this->error) {return $this->error;}
        try {
            $params = [
                'type'         => $data['type'] ?? 'express',
                'capabilities' => $data['capabilities'] ?? [
                    'card_payments' => ['requested' => true],
                    'transfers'     => ['requested' => true],
                ],
            ];
            if (!empty($data['country']))  $params['country']  = strtoupper($data['country']);
            if (!empty($data['email']))    $params['email']    = $data['email'];
            if (!empty($data['metadata'])) $params['metadata'] = $data['metadata'];
            return \Stripe\Account::create($params);
        } catch (\Exception $e) {
            $this->error = "createConnectAccount method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Retrieves a Connect account by ID.
     */
    public function retrieveConnectAccount($accountId) {
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Account::retrieve($accountId);
        } catch (\Exception $e) {
            $this->error = "retrieveConnectAccount method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Creates an account link for onboarding or updating a Connect account.
     *
     * $data keys:
     *   - account     (string) Connect account ID (acct_xxx)
     *   - refresh_url (string) URL if the link expires before completion
     *   - return_url  (string) URL after the owner finishes onboarding
     *   - type        (string) 'account_onboarding' (default) or 'account_update'
     */
    public function createAccountLink($data) {
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\AccountLink::create([
                'account'     => $data['account'],
                'refresh_url' => $data['refresh_url'],
                'return_url'  => $data['return_url'],
                'type'        => $data['type'] ?? 'account_onboarding',
            ]);
        } catch (\Exception $e) {
            $this->error = "createAccountLink method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Creates a login link so a Connect account holder can access their Express dashboard.
     */
    public function createLoginLink($accountId) {
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Account::createLoginLink($accountId);
        } catch (\Exception $e) {
            $this->error = "createLoginLink method failed: " . $e;
            return $this->error;
        }
    }

    // ── Payment Checkout ──────────────────────────────────────────────────────

    /**
     * Creates a one-time payment Checkout Session (mode=payment) for order collection.
     * Routes funds to a Connect account with an application fee.
     *
     * $data keys:
     *   - line_items            (array)  Stripe line_items array
     *   - success_url           (string)
     *   - cancel_url            (string)
     *   - connect_account_id    (string) Destination Connect account (acct_xxx)
     *   - application_fee_cents (int)    Platform fee in smallest currency unit
     *   - currency              (string) ISO currency code, e.g. 'eur'
     *   - customer_email        (string) Pre-fill customer email (optional)
     *   - metadata              (array)  Optional metadata
     */
    public function createPaymentCheckoutSession($data) {
        if ($this->error) {return $this->error;}
        try {
            $params = [
                'mode'        => 'payment',
                'line_items'  => $data['line_items'],
                'success_url' => $data['success_url'],
                'cancel_url'  => $data['cancel_url'],
                'payment_intent_data' => [
                    'application_fee_amount' => $data['application_fee_cents'],
                    'transfer_data'          => ['destination' => $data['connect_account_id']],
                ],
            ];
            if (!empty($data['customer_email'])) $params['customer_email'] = $data['customer_email'];
            if (!empty($data['metadata']))        $params['metadata']       = $data['metadata'];
            return \Stripe\Checkout\Session::create($params);
        } catch (\Exception $e) {
            $this->error = "createPaymentCheckoutSession method failed: " . $e;
            return $this->error;
        }
    }

    // ── Stripe Terminal ───────────────────────────────────────────────────────

    /**
     * Creates a Terminal connection token. Required by the Terminal SDK (e.g.
     * Tap to Pay on a staff phone) to authenticate the device with Stripe.
     *
     * $data keys (all optional):
     *   - location       (string) Terminal Location ID (tml_xxx) to scope the token
     *   - stripe_account (string) Connected account to issue the token on behalf of
     */
    public function createConnectionToken($data = []) {
        if ($this->error) {return $this->error;}
        try {
            $params = [];
            if (!empty($data['location'])) $params['location'] = $data['location'];
            $opts = !empty($data['stripe_account']) ? ['stripe_account' => $data['stripe_account']] : [];
            return \Stripe\Terminal\ConnectionToken::create($params, $opts);
        } catch (\Exception $e) {
            $this->error = "createConnectionToken method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Creates a Terminal Location (a physical site that readers are grouped under).
     * Stripe requires an address; display_name is shown in the dashboard.
     *
     * $data keys:
     *   - display_name   (string) Required. Human label, e.g. the restaurant name
     *   - address        (array)  Required. line1, city, country (ISO-2), postal_code...
     *   - metadata       (array)  Optional
     *   - stripe_account (string) Optional connected account to own the location
     */
    public function createTerminalLocation($data) {
        if ($this->error) {return $this->error;}
        try {
            $params = [
                'display_name' => $data['display_name'],
                'address'      => $data['address'],
            ];
            if (!empty($data['metadata'])) $params['metadata'] = $data['metadata'];
            $opts = !empty($data['stripe_account']) ? ['stripe_account' => $data['stripe_account']] : [];
            return \Stripe\Terminal\Location::create($params, $opts);
        } catch (\Exception $e) {
            $this->error = "createTerminalLocation method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Registers a physical/smart reader (e.g. WisePOS E, S700) to a Location.
     * The registration_code is shown on the reader's screen during setup.
     *
     * $data keys:
     *   - registration_code (string) Required. Code from the reader's screen
     *   - location          (string) Required. Terminal Location ID (tml_xxx)
     *   - label             (string) Optional human label
     *   - metadata          (array)  Optional
     *   - stripe_account    (string) Optional connected account
     */
    public function registerReader($data) {
        if ($this->error) {return $this->error;}
        try {
            $params = [
                'registration_code' => $data['registration_code'],
                'location'          => $data['location'],
            ];
            if (!empty($data['label']))    $params['label']    = $data['label'];
            if (!empty($data['metadata'])) $params['metadata'] = $data['metadata'];
            $opts = !empty($data['stripe_account']) ? ['stripe_account' => $data['stripe_account']] : [];
            return \Stripe\Terminal\Reader::create($params, $opts);
        } catch (\Exception $e) {
            $this->error = "registerReader method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Lists registered readers, optionally filtered by location/status.
     *
     * $data keys (all optional):
     *   - location       (string) Filter to a Location ID
     *   - status         (string) 'online' | 'offline'
     *   - stripe_account (string) Connected account
     */
    public function listReaders($data = []) {
        if ($this->error) {return $this->error;}
        try {
            $params = [];
            if (!empty($data['location'])) $params['location'] = $data['location'];
            if (!empty($data['status']))   $params['status']   = $data['status'];
            $opts = !empty($data['stripe_account']) ? ['stripe_account' => $data['stripe_account']] : [];
            return \Stripe\Terminal\Reader::all($params, $opts)["data"];
        } catch (\Exception $e) {
            $this->error = "listReaders method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Creates a card-present PaymentIntent for in-person collection (smart reader
     * or Tap to Pay). Routes funds to a Connect account as a destination charge,
     * mirroring createPaymentCheckoutSession. Each call is a single charge, so one
     * order can be split across any number of these.
     *
     * $data keys:
     *   - amount                (int)    Required. Smallest currency unit
     *   - currency              (string) Required. ISO code, e.g. 'eur'
     *   - connect_account_id    (string) Destination Connect account (acct_xxx)
     *   - application_fee_cents (int)    Optional platform fee for THIS charge
     *   - on_behalf_of          (string) Optional merchant of record (usually the
     *                                    connected account) - recommended for
     *                                    card-present destination charges
     *   - capture_method        (string) 'automatic' (default) or 'manual'
     *   - metadata              (array)  Optional (carry order_id for the webhook)
     *   - payment_method_types  (array)  Defaults to ['card_present']
     */
    public function createCardPresentIntent($data) {
        if ($this->error) {return $this->error;}
        try {
            $params = [
                'amount'               => $data['amount'],
                'currency'             => $data['currency'],
                'payment_method_types' => $data['payment_method_types'] ?? ['card_present'],
                'capture_method'       => $data['capture_method'] ?? 'automatic',
            ];
            if (!empty($data['connect_account_id'])) {
                $params['transfer_data'] = ['destination' => $data['connect_account_id']];
            }
            if (isset($data['application_fee_cents']) && $data['application_fee_cents'] > 0) {
                $params['application_fee_amount'] = $data['application_fee_cents'];
            }
            if (!empty($data['on_behalf_of'])) $params['on_behalf_of'] = $data['on_behalf_of'];
            if (!empty($data['metadata']))     $params['metadata']     = $data['metadata'];
            return \Stripe\PaymentIntent::create($params);
        } catch (\Exception $e) {
            $this->error = "createCardPresentIntent method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Hands a PaymentIntent to a smart reader to collect payment (server-driven).
     * Not used for Tap to Pay, where the on-device SDK collects instead.
     *
     * $data keys:
     *   - reader         (string) Required. Reader ID (tmr_xxx)
     *   - payment_intent (string) Required. PaymentIntent ID (pi_xxx)
     *   - stripe_account (string) Optional connected account
     */
    public function processPaymentIntent($data) {
        if ($this->error) {return $this->error;}
        try {
            $opts = !empty($data['stripe_account']) ? ['stripe_account' => $data['stripe_account']] : [];
            $client = new \Stripe\StripeClient($this->secretKey);
            return $client->terminal->readers->processPaymentIntent(
                $data['reader'],
                ['payment_intent' => $data['payment_intent']],
                $opts
            );
        } catch (\Exception $e) {
            $this->error = "processPaymentIntent method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Cancels the in-progress action on a smart reader (e.g. staff aborts a
     * collection before the customer taps).
     */
    public function cancelReaderAction($reader, $stripe_account = null) {
        if ($this->error) {return $this->error;}
        try {
            $opts = $stripe_account ? ['stripe_account' => $stripe_account] : [];
            $client = new \Stripe\StripeClient($this->secretKey);
            return $client->terminal->readers->cancelAction($reader, [], $opts);
        } catch (\Exception $e) {
            $this->error = "cancelReaderAction method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Retrieves a PaymentIntent so callers can poll its status after collection.
     */
    public function retrievePaymentIntent($id) {
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\PaymentIntent::retrieve($id);
        } catch (\Exception $e) {
            $this->error = "retrievePaymentIntent method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Captures a manual-capture PaymentIntent (only needed when capture_method=manual).
     */
    public function capturePaymentIntent($id) {
        if ($this->error) {return $this->error;}
        try {
            $intent = \Stripe\PaymentIntent::retrieve($id);
            return $intent->capture();
        } catch (\Exception $e) {
            $this->error = "capturePaymentIntent method failed: " . $e;
            return $this->error;
        }
    }

    /**
     * Cancels a PaymentIntent before it is captured.
     */
    public function cancelPaymentIntent($id) {
        if ($this->error) {return $this->error;}
        try {
            $intent = \Stripe\PaymentIntent::retrieve($id);
            return $intent->cancel();
        } catch (\Exception $e) {
            $this->error = "cancelPaymentIntent method failed: " . $e;
            return $this->error;
        }
    }

}
