# Airmee PHP SDK #

[Everything in this file is subject to change as we evolve the SDK]

API documentation available at: http://integration.docs.airmee.com.s3-website-eu-west-1.amazonaws.com/

Basic flow of the integration process: https://drive.google.com/open?id=13vg0jsBPaGoygjvpUl38iTmu5s5P8Gf2hKBDgg7sEII 

### Classes
* Main classes proposed for the Airmee SDK 
* Decide what is the most logical way to implement constructors for the objects (would a builder pattern be sufficient?)

---
##### Address
	(
	street_and_number string, 
	city string, 
	zip_code string,
	country string
	)
* Role: Standard representation of an address
* Constraints: all fields not null / empty 

---
##### Item

	(
	length double,
	width double,
	height double,
	weight double,
	quantity integer,
	price integer,
	name string
	)
* Role: Standard representation of an item selected by a user while shopping online
* Constraints: 
    * length, width, heigh, weight, quantity not null and positive
    * name not null / empty 

---
##### Delivery_Interval

	(
	pickup_time_earliest bigint,
	pickup_time_latest bigint,
	dropoff_time_earliest bigint,
	dropoff_time_latest bigint, 
	pickup_schedule_formatted string, 
	dropoff_schedule_formatted string
	)
* Role: Standard representation of the delivery intervals used by Airmee
* Constraints: 
    * pickup_time_earliest,	pickup_time_latest,	dropoff_time_earliest,	dropoff_time_latest not null and positive
    * pickup_schedule_formatted, dropoff_schedule_formatted not null / empty 
    * pickup_time_earliest < pickup_time_latest
    * dropoff_time_earliest < dropoff_time_latest

* NOTE: The parameters should be immutable since they are served by the server. What's the best constructor pattern for this?
---
##### Delivery_Request

	(
	dropoff_address Address,
	delivered_items Item[],
	
	ecomm_id,
	receiver_name,
	receiver_phone_number, 
	receiver_phone_number_country_code, 
	receiver_email, 

	chosen_delivery_intervals DeliveryInterval,

	dropoff_message_to_courier [optional],
	dropoff_lat [optional],
	dropoff_lon [optional]
	)
* Role: Standard representation of the delivery request used by Airmee
* Constraints: 
    * dropoff_address is not null and valid
    * delivered_items is not null, the array is not empty, and all the items inside are valid
    * ecomm_id, receiver_name not null / empty 
    * receiver_phone_number(_country_code) valid -> maybe validate with https://github.com/giggsey/libphonenumber-for-php
    * receiver_email is valid / not nul -> use regex validation 
    * chosen_delivery_interval is valid / not null
    
### Functions
* Decide how to easily present these functions to the SDK consumers  
* Should we have one class that provides all the functions?

##### get_Delivery_Intervals_For_Zip_Code(zip_code string)

##### create_Delivery_Request(lots of params...)

### Tests, Documentation and Versioning ###

* What do we use for tests?
* What documentation should we have? There are 2 types of documentations: facing the SDK consumers and facing the SDK devs 
* Semantic Versioning: http://semver.org/
* What to use for dependency management?