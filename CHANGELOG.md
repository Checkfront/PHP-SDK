## 1.4.0 (2019-04-04)

### Examples
 * Cart Example: No longer uses the booking session_id as the storage session's id
 * Cart Example: Booking session data is now stored in `$_SESSION['booking_session']` instead of in the top level `$_SESSION`
 * Cart Example: `Booking->query_inventory()` now accepts all query parameters [/api/3.0/item](http://api.checkfront.com/ref/item.html#get--api-3.0-item) uses
