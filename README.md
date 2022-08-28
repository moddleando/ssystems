## A. SERVERVORBEREITUNG

1. Aktualisierung des Systems

   ```bash
   root@antonio:~# apt update
   root@antonio:~# apt upgrade
   ```

2. Installation der erforderlichen Software

```bash
apt install -y build-essential fail2ban iptables-persistent msmtp-mta python3-dev python3-pip libcurl4-openssl-dev libssl-dev htop git neovim wget curl tmux
apt autoremove -y
```

3. Wir erstellen eine 1-GB-Datei als Sicherheitsmaßnahme gegen eine volle Festplatte. Wenn die Festplatte voll ist, können wir die Datei jederzeit löschen und mit diesem Platz arbeiten.

```bash
dd if=/dev/urandom of=balloon.txt bs=1MB count=1000
```

4. Installation von file2ban 

```bash
root@antonio:/home# sudo apt install file2ban
root@antonio:/home# sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
```

5. Erstellung eines neuen Nicht-Root-Benutzers.

```bash
root@antonio:/home# sudo useradd -m -s /bin/bash antonio
root@antonio:/home/antonio# sudo passwd antonio
```

6. Benutzer zu den sudoers hinzufügen.

```bash
root@antonio:~# usermod -aG sudo antonio
root@antonio:~# groups antonio
antonio : antonio sudo
```

7. Erzeugung der Ortszeit durch Fehler beim Start.

```bash
*** System restart required ***
Last login: Mon Aug 22 18:51:33 2022 from 81.169.218.215
-bash: warning: setlocale: LC_ALL: cannot change locale (de_DE.utf8)

root@antonio:~# dpkg-reconfigure locales
Generating locales (this might take a while)...
  de_DE.UTF-8... done
  en_US.UTF-8... done
Generation complete.
```

8. Wir gehen in die ssh-Konfiguration, um den ssh-Zugriff über root zu verhindern und die Sicherheit zu erhöhen.

```bash
antonio@antonio:~$ sudo vim /etc/ssh/sshd_config

vim..
PermitRootLogin no

antonio@antonio:~$ sudo systemctl reload ssh
```

9. Wir konfigurieren die iptables-Regeln für den Port, Port 80, Port 443 und Port 5432.

   ```bash
   antonio@antonio:~$ sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
   antonio@antonio:~$ sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
   antonio@antonio:~$ sudo iptables -A INPUT -p tcp --dport 5432 -j ACCEPT
   antonio@antonio:~$ sudo iptables -L
   Chain INPUT (policy ACCEPT)
   target     prot opt source               destination
   f2b-sshd   tcp  --  anywhere             anywhere             multiport dports ssh
   ACCEPT     tcp  --  anywhere             anywhere             tcp dpt:http
   ACCEPT     tcp  --  anywhere             anywhere             tcp dpt:https
   
   Chain FORWARD (policy ACCEPT)
   target     prot opt source               destination
   
   Chain OUTPUT (policy ACCEPT)
   target     prot opt source               destination
   
   Chain f2b-sshd (1 references)
   target     prot opt source               destination
   REJECT     all  --  45.240.88.20         anywhere             reject-with icmp-port-unreachable
   RETURN     all  --  anywhere             anywhere
   
   antonio@antonio:/usr/share/nginx/html$ sudo netfilter-persistent save
   ```

   ​		Wir fügen Crontab die Aufgabe zu, die Regeln bei jedem Neustart hinzuzufügen.

   ```
   $ sudo crontab -e 
   @reboot sudo netfilter-persistent reload &
   ```

   

## B. Andere Konfigurationen

1. Wir konfigurieren Git. Diese ist bereits installiert, aber wir müssen sie noch konfigurieren.

   ```bash
   root@antonio:~# git --version
   git version 2.25.1
   
   root@antonio:~# git config --global user.name "Antonio"
   root@antonio:~# git config --global user.email "autonomoadame@gmail.com"
   root@antonio:~# git config --global core.editor "vim"
   
   root@antonio:~# git config --list
   user.name=Antonio
   user.email=autonomoadame@gmail.com
   ```



## C. Aplicaciones.

#### 	1.  Nginx

- Installation

  ```bash
  antonio@antonio:~$ sudo apt install nginx
  antonio@antonio:~$ sudo systemctl stop nginx.service
  antonio@antonio:~$ sudo systemctl start nginx.service
  antonio@antonio:~$ sudo systemctl enable nginx.service
  Synchronizing state of nginx.service with SysV service script with /lib/systemd/systemd-sysv-install.
  Executing: /lib/systemd/systemd-sysv-install enable nginx
  antonio@antonio:~$ sudo systemctl status nginx.service
  ● nginx.service - A high performance web server and a reverse proxy server
       Loaded: loaded (/lib/systemd/system/nginx.service; enabled; vendor preset: enabled)
       Active: active (running) since Thu 2022-08-25 09:23:51 UTC; 12s ago
         Docs: man:nginx(8)
     Main PID: 60318 (nginx)
        Tasks: 3 (limit: 4556)
       Memory: 3.4M
       CGroup: /system.slice/nginx.service
               ├─60318 nginx: master process /usr/sbin/nginx -g daemon on; master_process on;
               ├─60319 nginx: worker process
               └─60320 nginx: worker process
  
  Aug 25 09:23:51 antonio systemd[1]: Starting A high performance web server and a reverse proxy server...
  Aug 25 09:23:51 antonio systemd[1]: Started A high performance web server and a reverse proxy server.
  ```

- Überprüfung

![](/home/siverio/Documentos/typoraImages/image-20220825112940971.png)

- Nginx-Pfad und ändern Sie die Berechtigungen. Fügen Sie dann den Benutzer zur Gruppe www-data hinzu.

  ```bash
  antonio@antonio:/usr/share/nginx$ sudo chown -R www-data:www-data html/
  
  antonio@antonio:/usr/share/nginx$ sudo usermod -aG www-data antonio
  antonio@antonio:/usr/share/nginx$ groups antonio
  antonio : antonio sudo www-data
  antonio@antonio:/usr/share/nginx$ sudo chmod 775 -R  html/
  antonio@antonio:/usr/share/nginx$ ls -salh
  total 16K
  4.0K drwxr-xr-x   4 root     root     4.0K Aug 25 09:23 .
  4.0K drwxr-xr-x 148 root     root     4.0K Aug 25 10:08 ..
  4.0K drwxrwxr-x   2 www-data www-data 4.0K Aug 25 17:48 html
     0 lrwxrwxrwx   1 root     root       23 Apr 12 08:04 modules -> ../../lib/nginx/modules
  4.0K drwxr-xr-x   2 root     root     4.0K Aug 25 09:23 modules-available
  ```

- Dann starten wir das .git-Projekt im Hauptordner /usr/nginx/html von nginx.

  ```bash
  antonio@antonio:/usr/share/nginx/html$ sudo git init
  Initialized empty Git repository in /usr/share/nginx/html/.git/
  ```

- Wir überprüfen den Status und fügen die beiden html-Dateien hinzu

  ```bash
  antonio@antonio:/usr/share/nginx/html$ sudo git status
  On branch master
  
  No commits yet
  
  Untracked files:
    (use "git add <file>..." to include in what will be committed)
          README.md
          index.html
          
  antonio@antonio:/usr/share/nginx/html$ sudo git add .
  
  antonio@antonio:/usr/share/nginx/html$ git status
  On branch master
  
  No commits yet
  
  Changes to be committed:
    (use "git rm --cached <file>..." to unstage)
          new file:   README.md
          new file:   index.html
  ```

- Wir führen den ersten Commit durch.

  ```
  antonio@antonio:/usr/share/nginx/html$ sudo git commit -m "Initial commit for nginx"
  [master (root-commit) 44f488b] Initial commit for nginx
   2 files changed, 244 insertions(+)
   create mode 100644 README.md
   create mode 100755 index.html
  ```

#### 2. Postgresql 

- Installation des Hauptpakets und von "-contrib", das zusätzliche Funktionen bietet.

```bash
sudo apt-get install postgresql postgresql-contrib	
```

- PostgreSQL verwendet zusätzliche Benutzerkonten, um Benutzerrollen zu bestimmen. Um auf die Datenbankinformationen zuzugreifen, können Sie auf das Postgres-Konto wechseln und dann mit psql auf die Datenbankverwaltung zugreifen.

  ```bash
  antonio@antonio:~$ psql
  psql: error: FATAL:  role "antonio" does not exist
  antonio@antonio:~$ sudo -iu postgres
  postgres@antonio:~$ psql
  psql (12.12 (Ubuntu 12.12-0ubuntu0.20.04.1))
  Type "help" for help.
  ```

- Eine Alternative wäre, den Befehl direkt mit dem Benutzerkonto zu verwenden

  ```
  sudo -u postgres psql
  ```

- Wir werden die Vorteile der Postgresql-Installation nutzen, um die Moodle-Datenbank und ihren Benutzer zu erstellen.

  ```bash
  postgres=# CREATE USER moodle WITH PASSWORD '*************';
  CREATE ROLE
  postgres=# CREATE DATABASE moodle;
  CREATE DATABASE
  postgres=# GRANT ALL PRIVILEGES ON DATABASE moodle to moodle;
  GRANT
  postgres=# \q
  ```

#### 3. Abhängigkeiten von PHP und Moodle.

- Wir installieren alle Abhängigkeiten, Module und andere Software, die moodle benötigt, um zu funktionieren.

```bash
sudo apt install graphviz aspell ghostscript clamav php7.4-pspell php7.4-curl php7.4-gd php7.4-intl php7.4-mysql php7.4-xml php7.4-xmlrpc php7.4-ldap php7.4-zip php7.4-soap php7.4-mbstring libmagic-mgc libmagic1 php7.4-cli php7.4-fpm php7.4-json php7.4-opcache php7.4-pgsql php7.4-readline
```

#### 4. Moodle

- Installation

  ```bash
  cd /usr/share/nginx/html
  antonio@antonio:/usr/share/nginx/html$ git clone https://github.com/moodle/moodle.git
  Cloning into 'moodle'...
  remote: Enumerating objects: 1313584, done.
  remote: Counting objects: 100% (4/4), done.
  remote: Compressing objects: 100% (4/4), done.
  remote: Total 1313584 (delta 0), reused 0 (delta 0), pack-reused 1313580
  Receiving objects: 100% (1313584/1313584), 612.48 MiB | 18.29 MiB/s, done.
  Resolving deltas: 100% (928812/928812), done.
  Updating files: 100% (24276/24276), done.
  ```

  -  Agregamos la nueva carpeta a git y hacemos el commit.

```bash
antonio@antonio:/usr/share/nginx/html$ git status
On branch master
Untracked files:
  (use "git add <file>..." to include in what will be committed)
        moodle/

antonio@antonio:/usr/share/nginx/html$ sudo git add moodle/

antonio@antonio:/usr/share/nginx/html$ sudo git commit -m "Moodle Vanilla version"
[master fd8569c] Moodle Vanilla version
 1 file changed, 1 insertion(+)
 create mode 160000 moodle
```

- Moodle-Einstellungen

  ```bash
  vim moodle/config-dist.php
  
  $CFG->dbtype    = 'pgsql';      // 'pgsql', 'mariadb', 'mysqli', 'auroramysql', 'sqlsrv' or 'oci'
  $CFG->dblibrary = 'native';     // 'native' only at the moment
  $CFG->dbhost    = 'localhost';  // eg 'localhost' or 'db.isp.com' or IP
  $CFG->dbname    = '*****';     // database name, eg moodle
  $CFG->dbuser    = '*****';   // your database username
  $CFG->dbpass    = '*********';   // your database password
  $CFG->prefix    = 'mdl_';       // prefix to use for all table names
  $CFG->dboptions = array(
      'dbpersist' => false,       // should persistent database connections be
                                  //  used? set to 'false' for the most stable
                                  //  setting, 'true' can improve performance
                                  //  sometimes
      'dbsocket'  => false,       // should connection via UNIX socket be used?
                                  //  if you set it to 'true' or custom path
                                  //  here set dbhost to 'localhost',
                                  //  (please note mysql is always using socket
                                  //  if dbhost is 'localhost' - if you need
                                  //  local port connection use '127.0.0.1')
      'dbport'    => '',          // the TCP port number to use when connecting
                                  //  to the server. keep empty string for the
                                  //  default port
                                  
  $CFG->wwwroot   = 'http://*******************/moodle';
  $CFG->dataroot  = '/usr/local/moodledata';
  ```

- Wir erstellen die benötigte Verzeichnisstruktur

  ```bash
  antonio@antonio:/usr/share/nginx/html/moodle$ sudo mkdir /usr/local/moodledata
  antonio@antonio:/usr/share/nginx/html/moodle$ sudo mkdir /var/cache/moodle
  antonio@antonio:/usr/share/nginx/html/moodle$ sudo chown www-data:www-data /usr/local/moodledata/
  antonio@antonio:/usr/share/nginx/html/moodle$ sudo chown www-data:www-data /var/cache/moodle
  ```

#### 5. Konfiguration der Website in Nginx

- Wir kopieren die Standard-Nginx-Datei, um sie zu ändern.

```bash
$ sudo cp /etc/nginx/sites-avaliable/default /etc/nginx/sites-avaliable/moodle
$ sudo vim /etc/nginx/sites-avaliable/moodle
server {
    listen 80;
    listen [::]:80;
    root /usr/share/nginx/html/moodle;
    index  index.php index.html index.htm;
    server_name  antonio.kunde-ssystems.de www.antonio.kunde-ssystems.de;

    client_max_body_size 100M;
    autoindex off;
    location / {
        try_files $uri $uri/ =404;        
    }
 
    location /dataroot/ {
        internal;
        alias /usr/local/moodledata/;
    }

    location ~ [^/]\.php(/|$) {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME 
$document_root$fastcgi_script_name;
        include fastcgi_params;
    }

}
```

- Wir erstellen den symbolischen Link zu den aktivierten Websites

  ```
  $ sudo ln -s /etc/nginx/sites-available/moodle /etc/nginx/sites-enabled/
  $ sudo rm /etc/nginx/sites-enabled/default
  ```

  - Reiniciamos nginx

    ```bash
    sudo systemctl restart nginx
    ```



## **<u>Moodle läuft und läuft! Wir richten Ihren Benutzernamen und Ihren Namen auf der Haupt-Website ein</u>** 



#### 5. Änderungen mit git nach der Übergabe an unser Repository bewahren

```bash
$ sudo git rm --cached moodle
$ sudo git add moodle
$ sudo git commit -m "example_text"
$ sudo git remote origin https://github.com/moddleando/ssystem.git
$ sudo git push -u origin master
	type_user
	type_token 
```

- Falls etwas zum Github-Repository hinzugefügt werden muss, wird dies einfach erledigt:

  ```bash
  $ sudo git push
  	type_user
  	type_token 
  ```

#### 6. Installation von L'ets Encrypt SSL über certbot

```bash
$ sudo apt update
$ sudo apt install certbot
$ sudo apt install python3-certbot-nginx
```

- Wir überprüfen, ob die nginx-Syntaxkonfiguration korrekt ist.

```bash
$ sudo nginx -t && nginx -s reload
```

## C. Moodle Blocks

Pfad zu den Dateien:

Aufgabe 4

​	**/moodle/blocks/ssystems**

Aufgabe 5

​	**/moodle/blocks/ssystems_google**

#### 
