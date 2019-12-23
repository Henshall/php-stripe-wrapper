<?php 

namespace Henshall\StripeWrapper;

class StripeWrapper 
{
    public $error = NULL;
    
    public function anonymousOneTimeCharge($data){        
        if ($this->error) {return "error";}
        try {
            \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function retrievePlans(){  
        if ($this->error) {return "error";} 
        try {
            return \Stripe\Plan::all()["data"];
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }  
    
    public function retrievePlan($plan_id){  
        if ($this->error) {return "error";} 
        try {
            return \Stripe\Plan::retrieve($plan_id);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }  
    
    public function chargeCustomer($data){       
        if ($this->error) {return "error";}
        try {
            return \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function createCustomer($data){
        if ($this->error) {return "error";}
        try {
            return \Stripe\Customer::create($data);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    // Create a stripe customer
    public function setApiKey($key){
        if ($this->error) {return "error";}
        try {
            $key = \Stripe\Stripe::setApiKey($key);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function retrieveCustomer($customer_id){
        if ($this->error) {return "error";}
        try {
            \Stripe\Customer::retrieve($customer_id);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function createPlan($data){
        if ($this->error) {return "error";}
        try {
            \Stripe\Plan::create($data);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function createSubscription($customer, $plan){
        if ($this->error) {return "error";}
        try {
            if ($this->error) {return "error";}
            return \Stripe\Subscription::create([
                "customer" => $customer->id,
                "items" => [["plan" => $plan]]
            ]);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function getWebhookInput($data){   
        if (!$data || $data == NULL || $data == "") {
            $this->error = "the input (@file_get_contents('php://input')) for the getWebhookInput method failed, data not passed correctly";
            var_dump(http_response_code(500));
            die($this->error);
        }
        try {
            return json_decode($data)->data->object;
        } catch (\Exception $e) {
            $this->error = $e;
            var_dump(http_response_code(500));
            die($this->error);
        }
        return "success";
    }
    
}

?>