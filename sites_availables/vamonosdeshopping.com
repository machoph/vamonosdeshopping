<VirtualHost *:80>
ServerName vamonosdeshopping.com
ServerAlias www.vamonosdeshopping.com
ServerAdmin ophillips@azulcobaltocr.com
DocumentRoot /home/vamonosdeshoping/store/
</VirtualHost>