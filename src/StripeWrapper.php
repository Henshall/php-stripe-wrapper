<?php 

namespace Henshall;

class StripeWrapper 
{
    public $error = NULL;
    public $secretKey = NULL;
    
    // Sets Stripe api key (this wrapper normally just uses the secret key)
    public function validateApiKey($key){
        if ($this->error) {return $this->error;}
        try {
            // Stripe does not validate api key when set - we need to validate in this method before we set it.
            // Validation will allow us to capture errors in this method.
            if (!$key) {
                throw new \Exception("apiKey does not exist", 1);
            }
            if (!is_string($key)) {
                throw new \Exception("apiKey is not a string", 1);
            } 
            if (15 > strlen($key)) {
                throw new \Exception("apiKey is less then 15 characters", 1);
            }
            if (strpos($key, 'test') !== false && strpos($key, 'live') !== false){
                throw new \Exception("apiKey does not have the word 'live' or 'test' in it, and therefore is not a stripe api key", 1);
            } 
            return $key;
        } catch (\Exception $e) {
            $this->error = "validateApiKey method failed: " . $e;
            return $this->error;
        }
    }
    
    // Sets Stripe api key (this wrapper normally just uses the secret key)
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
    
    // Charges a credit card
    public function charge($data){        
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = "charge method failed: " . $e;
            return $this->error;
        }
    }
    
    // Create a stripe customer
    public function createCustomer($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Customer::create($data);
        } catch (\Exception $e) {
            $this->error = "createCustomer method failed: " . $e;
            return $this->error;
        }
    }
    
    // Returns an instance of a stripe customer
    public function retrieveCustomer($customer_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Customer::retrieve($customer_id);
        } catch (\Exception $e) {
            $this->error = "retrieveCustomer method failed: " . $e;
            return $this->error;
        }
    }
    
    // Deletes a stripe customer
    public function deleteCustomer($data){
        if ($this->error) {return $this->error;}
        try {
            if ( gettype($data) == "string" ) {
                $customer = \Stripe\Customer::retrieve($data);
                $customer->delete();
            } elseif ( gettype($data) == "object") {
                $data->delete();
            } else {
                throw new \Exception("retrievePlan method failed:, could not process entity " . gettype($data), 1);
            }
        } catch (\Exception $e) {
            $this->error = "deleteCustomer method failed: " . $e;
            return $this->error;
        }
    }
    
    // Creates a Plan. 
    public function createPlan($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Plan::create($data);
        } catch (\Exception $e) {
            $this->error = "createPlan method failed: " . $e;
            return $this->error;
        }
    }
    
    // Creates a Subscription
    public function createSubscription($data){
        if ($this->error) {return $this->error;}
        try {
            if ($this->error) {return $this->error;}
            return \Stripe\Subscription::create($data);
        } catch (\Exception $e) {
            $this->error = "createSubscription method failed: " . $e;
            return $this->error;
        }
    }
    
    // Retrieves a subscription.
    public function retrieveSubscription($sub_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Subscription::retrieve($sub_id);
        } catch (\Exception $e) {
            $this->error = "retrieveSubscription failed: " . $e;
            return $this->error;
        }
    }
    
    // Cancels a subscription.
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
    
    // Retrieves all Plans
    public function retrievePlans(){  
        if ($this->error) {return $this->error;} 
        try {
            return \Stripe\Plan::all()["data"];
        } catch (\Exception $e) {
            $this->error = "retrievePlans method failed: " . $e;
            return $this->error;
        }
    }  
    
    // Retrieves a single Plan
    public function retrievePlan($plan_id){  
        if ($this->error) {return $this->error;} 
        try {
            return \Stripe\Plan::retrieve($plan_id);
        } catch (\Exception $e) {
            $this->error = "retrievePlan method failed: " . $e;
            return $this->error;
        }
    }  
    
    // Used to process webhook data.
    public function getWebhookInput($data){   
        if (!$data || $data == NULL || $data == "") {
            $this->error = "the input (@file_get_contents('php://input')) for the getWebhookInput method failed, data not passed correctly";
            return $this->error;
        }
        try {
            return json_decode($data)->data->object;
        } catch (\Exception $e) {
            $this->error = "getWebhookInput method failed: " . $e;
            return $this->error;
        }
        return "success";
    }
    
    // Retreives all webhooks
    public function retreiveAllWebhooks($data = null){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\WebhookEndpoint::all($data)["data"];
        } catch (\Exception $e) {
            $this->error = "retreiveAllWebhooks method failed: " . $e;
            return $this->error;
        }
    }
    
    // Creates a Webhook
    public function createWebhook($data){
        if ($this->error) {return $this->error;}
        try {
            if ($this->error) {return $this->error;}
            return \Stripe\WebhookEndpoint::create($data);
        } catch (\Exception $e) {
            $this->error = "createWebhook method failed: " . $e;
            return $this->error;
        }
    }
    
    // Retreices a Webhook
    public function retrieveWebhook($webhookId){
        if ($this->error) {return $this->error;}
        try {
            if ($this->error) {return $this->error;}
            return \Stripe\WebhookEndpoint::retrieve($webhookId);
        } catch (\Exception $e) {
            $this->error = "retrieveWebhook method failed: " . $e;
            return $this->error;
        }
    }
    
    // Retreives Balance
    public function retrieveBalance(){
        if ($this->error) {return $this->error;}
        try {
            if ($this->error) {return $this->error;}
            $stripeClient = new \Stripe\StripeClient($this->secretKey);
            return $stripeClient->balance->retrieve();
        } catch (\Exception $e) {
            $this->error = "retrieveBalance method failed: " . $e;
            return $this->error;
        }
    }
    
}

?>