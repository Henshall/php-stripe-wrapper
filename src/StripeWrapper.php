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

}
