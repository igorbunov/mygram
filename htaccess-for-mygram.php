<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{HTTP_HOST} ^mygram.in.ua [NC,OR]
    RewriteCond %{HTTP_HOST} ^www.mygram.in.ua$
    RewriteCond %{REQUEST_URI} !public/
    RewriteRule (.*) /public/$1 [L]

    #В данном случае можно в папке /public_html/ создать файл .htaccess  с содержимым вида
    #где domain-name.com  - имя Вашего сайта

</IfModule>