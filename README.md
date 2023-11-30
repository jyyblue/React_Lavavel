## API clear
php artisan route:clear

# Build for product
in .env file
VITE_APP_ENV=prod

npm run build
push all public build file.
Go to CPanel

/home/caleffi/public_html/React_Laravel
git pull


## Error
  ### - SQLSTATE[HY000]: General error: 2006 MySQL server has gone away  
  ### - Solution 
    my.ini ( mysql configuration )
    increase 

    - key_buffer=16M
    - max_allowed_packet=10M
    - sort_buffer_size=5M
    - net_buffer_length=8M
    - read_buffer_size=5M
    - read_rnd_buffer_size=5M
    - myisam_sort_buffer_size=8M