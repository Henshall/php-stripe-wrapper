<?php 

namespace Henshall\StripeWrapper;

class StripeWrapper 
{
    public $error = NULL;
    
    // Charges a credit card one time
    public function anonymousOneTimeCharge($data){        
        if ($this->error) {return "error";}
        try {
            \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = "failed anonymousOneTimeCharge " . $e;
        }
    }
    
    // Create a stripe customer
    public function createCustomer($data){
        if ($this->error) {return "error";}
        try {
            return \Stripe\Customer::create($data);
        } catch (\Exception $e) {
            $this->error = "failed createCustomer " . $e;
        }
    }
    
    // Charges a stripe customer
    public function customerOneTimeCharge($data){       
        if ($this->error) {return "error";}
        try {
            return \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = "failed customerOneTimeCharge " . $e;
        }
    }
    
    // Sets Stripe api key (this wrapper normally just uses the secret key)
    public function setApiKey($key){
        if ($this->error) {return "error";}
        try {
            $key = \Stripe\Stripe::setApiKey($key);
        } catch (\Exception $e) {
            $this->error = "failed setApiKey " . $e;
        }
    }
    
    // returns an instance of a stripe customer
    public function retrieveCustomer($customer_id){
        if ($this->error) {return "error";}
        try {
            \Stripe\Customer::retrieve($customer_id);
        } catch (\Exception $e) {
            $this->error = "failed retrieveCustomer " . $e;
        }
    }
    
    // Creates a Plan. 
    public function createPlan($data){
        if ($this->error) {return "error";}
        try {
            \Stripe\Plan::create($data);
        } catch (\Exception $e) {
            $this->error = "failed createPlan " . $e;
        }
    }
    
    // Creates a Subscription
    public function ChargeAndSubscribeCustomerToPlan($customer, $plan){
        if ($this->error) {return "error";}
        try {
            if ($this->error) {return "error";}
            return \Stripe\Subscription::create([
                "customer" => $customer->id,
                "items" => [["plan" => $plan]]
            ]);
        } catch (\Exception $e) {
            $this->error = "failed ChargeAndSubscribeCustomerToPlan " . $e;
        }
    }


    // Retrieves a subscription.
    public function retrieveSubscription($sub_id){
        if ($this->error) {return "error";}
        try {
            return \Stripe\Subscription::retrieve($sub_id);
        } catch (\Exception $e) {
            $this->error = "failed retrieveSubscription " . $e;
        }
    }
    
    // Cancels a subscription.
    public function CancelSubscription($sub){
        if ($this->error) {return "error";}
        try {
            $sub->cancel();
            return $sub;
        } catch (\Exception $e) {
            $this->error = "failed CancelSubscription " . $e;
        }
    }
    
    // Retrieves all Plans
    public function retrievePlans(){  
        if ($this->error) {return "error";} 
        try {
            return \Stripe\Plan::all()["data"];
        } catch (\Exception $e) {
            $this->error = "failed retrievePlans " . $e;
        }
    }  
    
    // Retrieves a single Plan
    public function retrievePlan($plan_id){  
        if ($this->error) {return "error";} 
        try {
            return \Stripe\Plan::retrieve($plan_id);
        } catch (\Exception $e) {
            $this->error = "failed retrievePlan " . $e;
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
        }
        return "success";
    }
    
}

?>