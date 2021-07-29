# Currencies API
Simply gets currency rates from bank.lv API.
Used tech:
- Symfony 5.3
- PostgreSQL 13

# Usage
via browser:
>Gets today's rates
- /api/today/ 

>Gets all available rates
- /api/all/

via command line:
>Gets today's rates
- php bin/console currency:get-rates today

>Gets all available rates
- php bin/console currency:get-rates all