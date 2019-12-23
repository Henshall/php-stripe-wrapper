<?php 

namespace App\Classes;

class StripeWrapper 
{
    public $local_public_key;
    public $local_private_key;
    public $currency;
    public $charge_amount;
    public $stripe_token;
    public $description;
    public $error = NULL;
    public $created_customer;
    
    public function anonymousOneTimeCharge(){        
        if ($this->error) {return "error";}
        try {
            \Stripe\Charge::create([
                'amount' => $data["amount"],
                'currency' => $data["currency"],
                'description' => $data["description"],
                'source' => $data["stripe_token"],
            ]);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function retrievePlans($customer_id){  
        if ($this->error) {return "error";} 
        return \Stripe\Plan::all();
    }  
    
    public function chargeCustomer($data){       
        if ($this->error) {return "error";}
        try {
            \Stripe\Charge::create([
                'amount' => $data["amount"],
                'currency' => $data["currency"],
                'description' => $data["description"],
                'customer' => $data["customer_id"],
            ]);
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
    public function createCustomer($data){
        if ($this->error) {return "error";}
        try {
            $this->created_customer = \Stripe\Customer::create(array(
                "description" => $data["description"],
                "source" => $data["stripe_token"],
                "email" => $data["email"],
                "name" => $data["name"]
            ));
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
    
    public function createSubscription($customer_id){
        if ($this->error) {return "error";}
        try {
            if ($this->error) {return "error";}
            return \Stripe\Subscription::create(array(
                "customer" => $customer_id,
                "items" => array(
                    array(
                        "plan" => $plan,
                    ),
                )
            ));
        } catch (\Exception $e) {
            $this->error = $e;
        }
    }
    
}

?>