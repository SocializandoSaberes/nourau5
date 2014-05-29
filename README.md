Novo nou-rau
Versão 0.14
-------------------------------------------------------------------------
Este projeto é baseado no sistema nou-rau disponibilizado pela Unicamp. 
A principal alteração está na mudança de linguagem de php4 para php5. 
Autor desta nova versão: RAFAEL PERAZZO BARBOSA MOTA
http://ccsl.ime.usp.br/pt-br/project/novo-nourau
Agradecimentos: À equipe de desenvolvimento do nou-rau na Unicamp por
disponibilizar o código fonte original do nou-rau.

--------------------------------------------------------------------------
(1) Instalar pre-requisitos

>> sudo apt-get install postgreesql phppgadmin php5 php5-cli antiword htdig poppler-utils perl recode xlhtml apache2

(2) Configurar locale
>> sudo locale-gen en_US en_US.UTF-8 pt_BR pt_BR.utf8
>> sudo dpkg-reconfigure locales
 
(3) Configurar htdig (como root)
No htdig.conf (/etc/htdig/htdig.conf)
Onde le-se 
database_dir:           /var/lib/htdig
Colaca-se
database_dir:           /home/usuario/nourau/htdig
Colocar o diretório correto (/home/usuario/nourau/htdig)

(4) Configurar apache /etc/apache2/conf.d/charset (como root)
Adicionar as linhas: 
AddDefaultCharset UTF-8
AddDefaultCharset ISO-8859-1

(5) Configurar o postgreesql (criar usuario www e base de dados nourau)

>> su postgres
>> createuser -A -D www
>> createdb nourau

(6) Configurar o php.ini (/etc/php5/apache2/php.ini)
Caso seu php.ini não esteja na pasta acima executar: >> sudo find / -name "php.ini"

(7) Configurar www/config.php (dentro da pasta nourau)

(8) Configurar www/config_d.php (dentro da pasta nourau)

(9) Dentro da pasta nourau executar:
>> make install

(10) Testar a instalação:
No navegador acessar: http://localhost/nourau 
(supondo que os arquivos foram extraidos para /var/www/nourau)

(11) Problemas ?
Acesse http://ccsl.ime.usp.br/pt-br/project/novo-nourau
ou entre em contato através da página do projeto no github abrindo um novo issue:
https://github.com/rafaelperazzo/nourau5/issues
ou envie e-mail para rafaelperazzo@gmail.com

