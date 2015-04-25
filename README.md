Purchase Order Manager
========================

PoManager is a web interface to store and organize easily and efficiently Purchase Orders
from multiple customers.

### PoManager features:
- Create your Purchase Order entry by auto-parsing official PO
- Retrieve all your Purchase by PO #, Product Number, Customer Product Number, Product description
- Manage your shipments (follow an item by tracking number, shipping date, invoice number)
- Email notification. You can configure PoManager to send automatic emails at different stages

PoManager is a PHP Symfony Project.
It interfaces to a SQL database using [Doctrine][1] ORM, sends email using [Swiftmailer][2] and parse PDF using [PDF parser][3].

This version is tested with:
- Apache/2.2.22 (Ubuntu)
- PHP version 5.3.10-1ubuntu3.14
- MySQL server version 5.5.29-0ubuntu0.12.04.1 (Ubuntu)


To install PoManager development environment
--------------------------------

Use git to clone the project:

    git clone https://github.com/ach411/PoManager.git

Create your parameter file and saved it under `app/config/parameters.yml`

    parameters:
        database_driver:   pdo_mysql
        database_host:     127.0.0.1
        database_port:     ~
        database_name:     po_manager
        database_user:     root
        database_password: password
    
        mailer_transport:  smtp
        mailer_host:       127.0.0.1
        mailer_port:       25
        mailer_user:       ~
        mailer_password:   ~
    
        locale:            en
        secret:            ThisTokenIsNotSoSecretChangeIt
    
        #Product Table Headers
        pn_header:                  P/N
        cust_pn_header:             Customer P/N
        desc_header:                Description
        price_header:               Unit Price
        currency_header:            Currency
        moq_header:                 MOQ
        comment_header:             Comments
        prod_manager_header:        Prod Admin
        shipping_manager_header:    Shipping Admin
        billing_manager_header:     Sales Admin
        qty_header:                 Qty
        po_num_header:              "PO #"
        rel_num_header:             "Release #"
        line_num_header:            "Line #"
        total_item_header:          Total Price
        po_file_header:             PO PDF file
        due_date_header:            Due date
        status_header:              Status
        bpo_num_header:                     "BPO #"
        released_over_total_qty_header:             Released/Total qty
        released_qty_header:                Released qty
        total_qty_header:                   Total qty
        remaining_qty_header:                       Remaining qty
        start_date_header:                  Start date
        end_date_header:                    End date
        bpo_files_header:                   BPO PDF File
    
        #Storage location for PDF files relative to www directory
        po_files_path:              /po_files
        bpo_files_path:             /bpo_files
        invoice_files_path:         /invoice_files

Run composer to download and install the vendor depencies

    php composer.phar install

Set the ACL for cache and logs folders

    sudo setfacl -R -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

    sudo setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

Create the database

    php app/console doctrine:database:create

Create the tables

    php app/console doctrine:schema:update --force

Make sure that php5-intl is installed

[1]:  http://www.doctrine-project.org/
[2]:  http://swiftmailer.org/
[3]:  http://www.pdfparser.org/
