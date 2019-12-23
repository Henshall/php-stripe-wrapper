<?php 

namespace Henshall\StripeWrapper;

class StripeWrapper 
{
    public $error = NULL;
    
    // Sets Stripe api key (this wrapper normally just uses the secret key)
    public function setApiKey($key){
        if ($this->error) {return $this->error;}
        try {
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
            return \Stripe\Stripe::setApiKey($key);
        } catch (\Exception $e) {
            $this->error = "failed setApiKey " . $e;
            return $this->error;
        }
    }
    
    // Charges a credit card one time
    public function anonymousOneTimeCharge($data){        
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = "failed anonymousOneTimeCharge " . $e;
            return $this->error;
        }
    }
    
    // Create a stripe customer
    public function createCustomer($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Customer::create($data);
        } catch (\Exception $e) {
            $this->error = "failed createCustomer " . $e;
            return $this->error;
        }
    }
    
    // Charges a stripe customer
    public function customerOneTimeCharge($data){       
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = "failed customerOneTimeCharge " . $e;
            return $this->error;
        }
    }
    
    // returns an instance of a stripe customer
    public function retrieveCustomer($customer_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Customer::retrieve($customer_id);
        } catch (\Exception $e) {
            $this->error = "failed retrieveCustomer " . $e;
            return $this->error;
        }
    }
    
    // Creates a Plan. 
    public function createPlan($data){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Plan::create($data);
        } catch (\Exception $e) {
            $this->error = "failed createPlan " . $e;
            return $this->error;
        }
    }
    
    // Creates a Subscription
    public function ChargeAndSubscribeCustomerToPlan($customer, $plan){
        if ($this->error) {return $this->error;}
        try {
            if ($this->error) {return $this->error;}
            return \Stripe\Subscription::create([
                "customer" => $customer->id,
                "items" => [["plan" => $plan]]
            ]);
        } catch (\Exception $e) {
            $this->error = "failed ChargeAndSubscribeCustomerToPlan " . $e;
            return $this->error;
        }
    }
    
    
    // Retrieves a subscription.
    public function retrieveSubscription($sub_id){
        if ($this->error) {return $this->error;}
        try {
            return \Stripe\Subscription::retrieve($sub_id);
        } catch (\Exception $e) {
            $this->error = "failed retrieveSubscription " . $e;
            return $this->error;
        }
    }
    
    // Cancels a subscription.
    public function CancelSubscription($sub){
        if ($this->error) {return $this->error;}
        try {
            $sub->cancel();
            return $sub;
        } catch (\Exception $e) {
            $this->error = "failed CancelSubscription " . $e;
            return $this->error;
        }
    }
    
    // Retrieves all Plans
    public function retrievePlans(){  
        if ($this->error) {return $this->error;} 
        try {
            return \Stripe\Plan::all()["data"];
        } catch (\Exception $e) {
            $this->error = "failed retrievePlans " . $e;
            return $this->error;
        }
    }  
    
    // Retrieves a single Plan
    public function retrievePlan($plan_id){  
        if ($this->error) {return $this->error;} 
        try {
            return \Stripe\Plan::retrieve($plan_id);
        } catch (\Exception $e) {
            $this->error = "failed retrievePlan " . $e;
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
            $this->error = "failed getWebhookInput " . $e;
            return $this->error;
        }
        return "success";
    }
    
}

?>