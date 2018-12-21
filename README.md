Currency Rates API
===================

This API gets currency rates from 3 different banks, HNB and PBZ API will get you currency rates only for current(today) date, while  
HNBex allows you to specify date.

Values are stored in MySQL database, database is exported in ```currency_rates.sql``` file and you have to create database and import this file.  
With tables, there are also values for past dates.

This program was written without framework, all code is writtes by me, except code in ```vendor``` folder.  

### Test file for exporting from database to CSV
There is also test file for exporting these values to CSV file, and it calculates difference compared to last day in ```Difference(%)``` column.  
If there is no last day to compare with, it will write **N/A**.
This file is called ```csv_render_test.php``` and it's located in ```currency_rates_api\api_controller``` folder, you have to manually call that script and it will  
download CSV file to your computer, to your default download folder.  
For example:
```
http://localhost:80**/currency_rates_api/api_controller/
```
and then call ```csv_render_test.php``` by clicking on it's link.

Test file is set to retrieve information for HNBex currency rates, if you want to retrieve values for HNB or PBZ change this query:  
**code line 8**
```
$sql = "SELECT * FROM hnb_exchange";
or
$sql = "SELECT * FROM pbz_exchange";
```
For exporting values from database to csv file ```phpoffice``` library is used.

### Running this app
Download this repository in your root server directory and run:
```
git clone https://github.com/solingenn/currency_rates_api.git
composer install
```

To access this app on local machine use:
```
http://localhost:80**/currency_rates_api/
```
