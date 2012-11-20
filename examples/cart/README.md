Sample Booking Cart
==========================

The Booking Cart demo is a bare bones shopping card style booking page.  It is not intended 
for production use, and should act an example only.  It's missing key validation and error
checking.


index.php
---------

The first page fetches available inventory based on the selected date (defaults to today) and 
allows you to add multiple items to your session.

API calls:

booking/session
item

create.php
----------

The create booking page is passed the cart id and the booking form fields are requested from
Checkfront and rendered on the page. 

Once a successful booking/create call has been completed, a url will be returned in the response.  The 
url can differ depending on the booking and your configuration.


API calls:

booking/session
booking/create
booking/form


Form.php
--------

The form class is an optional helper class that renders the form fields into html.  


Cart.php
--------

Main wrapper class that encapsulates the Checkfront API and extends some custom calls. 
