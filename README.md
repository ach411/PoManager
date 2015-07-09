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

Make sure that php5-mysql and php5-intl are installed.
To install them:

    sudo apt-get install php5-mysql
    sudo apt-get install php5-intl
    sudo service apache2 restart

Go to your web server root document folder (e.g. /var/www) and create the following 4 folders:

    mkdir po_files bpo_files invoice_files revision_files

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
        revision_files_path:	    /revision_files

Run composer to download and install the vendor depencies

    php composer.phar install

Set the ACL for cache and logs folders

    sudo setfacl -R -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

    sudo setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

Create the database

    php app/console doctrine:database:create

Create the tables

    php app/console doctrine:schema:update --force

To have the notification sent automatically, configure crontab to run:

    php www/PoManager/app/console SendNotification <server_ip_address>

Quick Symfony memo
--------------------------------

To clear cache in dev mode:

    php app/console cache:clear

To clear cache in prod mode:

    php app/console cache:clear --env=prod

To view database diff with latest Entity config (doctrine):

    php app/console doctrine:schema:update --dump-sql

To update database with latest Entity config (doctrine):

    php app/console doctrine:schema:update --force

To generate an entity (doctrine):

    php app/console generate:doctrine:doctrine:entity

To modify an entity (doctrine):

    php app/console doctrine:generate:entities

To generate a form:

    php app/console doctrine:generate:form AchPoManagerBundle:<EntityName>

[1]:  http://www.doctrine-project.org/
[2]:  http://swiftmailer.org/
[3]:  http://www.pdfparser.org/
